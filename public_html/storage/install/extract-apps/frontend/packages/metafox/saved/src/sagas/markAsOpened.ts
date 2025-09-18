/**
 * @type: saga
 * name: saga.saveditems.markAsOpened
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* markAsOpened(
  action: ItemLocalAction<{ identity: string; showFeedbackSuccess?: boolean }>
) {
  const {
    payload: { identity, showFeedbackSuccess = true }
  } = action;

  const item = yield* getItem(identity);

  if (!item) return;

  const config = yield* getItemActionConfig(item, 'markAsOpened');

  const { apiClient, compactUrl, compactData, getPageParams } =
    yield* getGlobalContext();

  const params: any = getPageParams();

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_opened: value } = item;

  if (value) return;

  let collection_id = undefined;

  if (item.current_collection_id) {
    collection_id = item.current_collection_id;
  }

  if (params?.collection_id) {
    collection_id = params.collection_id;
  }

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, {
        saved_id: item.id,
        collection_id
      })
    });

    const data = response?.data?.data;

    yield* patchEntity(identity, { is_opened: data.is_opened });

    if (showFeedbackSuccess) {
      yield* handleActionFeedback(response);
    }
  } catch (error) {
    yield* patchEntity(identity, { is_opened: value });
    yield* handleActionError(error);
  }
}

function* markAsUnOpened(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  if (!item) return;

  const config = yield* getItemActionConfig(item, 'markAsUnOpened');

  const { apiClient, compactUrl, compactData, getPageParams } =
    yield* getGlobalContext();

  const params: any = getPageParams();

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_opened: value } = item;

  if (!value) return;

  let collection_id = undefined;

  if (item.current_collection_id) {
    collection_id = item.current_collection_id;
  }

  if (params?.collection_id) {
    collection_id = params.collection_id;
  }

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, {
        saved_id: item.id,
        collection_id
      })
    });

    const data = response?.data?.data;

    yield* patchEntity(identity, { is_opened: data.is_opened });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_opened: value });
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('saved/markAsOpened', markAsOpened),
  takeEvery('saved/markAsUnOpened', markAsUnOpened)
];

export default sagas;
