/**
 * @type: saga
 * name: sticker.saga.sticker
 */

import {
  fulfillEntity,
  getGlobalContext,
  getResourceAction,
  ItemLocalAction,
  getItemActionConfig,
  getItem,
  handleActionFeedback,
  handleActionError,
  patchEntity,
  PAGINATION_INIT,
  PAGINATION_PUSH,
  PAGINATION_DELETE
} from '@metafox/framework';
import { put, select, takeLatest, takeEvery } from 'redux-saga/effects';
import { STICKER, STICKER_SET } from '@metafox/sticker/constant';
import { getAllStickerSet, getMyStickerSet } from '../selectors';
import { AppState } from '../types';
import qs from 'query-string';

export function* openStickerPickerDialog() {
  const { loaded }: AppState['myStickerSet'] = yield select(getMyStickerSet);

  if (loaded) return;

  const { dispatch } = yield* getGlobalContext();

  const configMySet = yield* getResourceAction(
    STICKER,
    STICKER_SET,
    'viewMyStickerSet'
  );

  dispatch({
    type: PAGINATION_INIT,
    payload: {
      apiUrl: configMySet.apiUrl,
      apiParams: { ...configMySet.apiParams, limit: 8 },
      pagingId: `${configMySet.apiUrl}?${qs.stringify(configMySet.apiParams)}`,
      canLoadMore: true
    }
  });
 
}

export function* openDialogSticker() {
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'dialog.sticker.manager',
    props: {
      itemView: 'sticker.ui.stickerSet'
    }
  });
}

export function* fetchAllStickerSet() {
  const { loaded }: AppState['stickerSet'] = yield select(getAllStickerSet);

  if (loaded) return;

  const { apiClient, normalization } = yield* getGlobalContext();
  const config = yield* getResourceAction(STICKER, STICKER_SET, 'viewAll');

  const response = yield apiClient.request({
    method: 'GET',
    url: config.apiUrl,
    params: config.apiParams
  });

  const data = response.data?.data;
  const result = normalization.normalize(data);
  yield* fulfillEntity(result.data);

  yield put({
    type: 'sticker/stickerSet/FULFILL',
    payload: { data: result.ids }
  });
}

export function* addToMyList(action: ItemLocalAction<{ onSuccess: any }>) {
  const {
    payload: { identity, onSuccess }
  } = action;
  const item = yield* getItem(identity);
  const { apiClient, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'addToMyList');
  const configMySet = yield* getResourceAction(
    STICKER,
    STICKER_SET,
    'viewMyStickerSet'
  );

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      data: compactData(apiParams, item)
    });
    yield* patchEntity(identity, { is_added: true });

    yield put({
      type: PAGINATION_PUSH,
      payload: {
        data: [identity],
        pagingId: `${configMySet.apiUrl}?${qs.stringify(configMySet.apiParams)}`
      }
    });

    yield put({
      type: 'sticker/myStickerSet/addItem',
      payload: { data: identity }
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }

  if (onSuccess) onSuccess();
}

export function* removeFromMyList(action: ItemLocalAction<{ onSuccess: any }>) {
  const {
    payload: { identity, onSuccess }
  } = action;
  const item = yield* getItem(identity);

  const config = yield* getItemActionConfig(item, 'removeFromMyList');
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const configMySet = yield* getResourceAction(
    STICKER,
    STICKER_SET,
    'viewMyStickerSet'
  );

  try {
    const { apiMethod, apiUrl } = config;

    const response = yield apiClient.request({
      method: apiMethod || 'DELETE',
      url: compactUrl(apiUrl, item)
    });

    yield put({
      type: PAGINATION_DELETE,
      payload: {
        identity,
        prefixPagingId: `${configMySet.apiUrl}?${qs.stringify(
          configMySet.apiParams
        )}`
      }
    });

    yield* patchEntity(identity, { is_added: false });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }

  if (onSuccess) onSuccess();
}

const sagas = [
  takeLatest('sticker/openStickerPickerDialog', openStickerPickerDialog),
  takeLatest('sticker/openDialogSticker', openDialogSticker),
  takeLatest('sticker/fetchAllStickerSet', fetchAllStickerSet),
  takeEvery('sticker/addStickerSet', addToMyList),
  takeEvery('sticker/removeStickerSet', removeFromMyList)
];

export default sagas;
