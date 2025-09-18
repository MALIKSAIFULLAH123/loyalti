/**
 * @type: saga
 * name: updatedEvent
 */

import {
  LocalAction,
  viewItem,
  getItem,
  getItemActionConfig,
  getGlobalContext,
  makeDirtyPaging,
  handleActionFeedback,
  handleActionError,
  fulfillEntity,
  patchEntity, ItemLocalAction,
  handleActionConfirm
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import { isFunction } from 'lodash';

function* updatedEvent({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('event', 'event', id);
}

function* eventActive({ payload }) {
  yield put({ type: 'event/active', payload });
}

export function* updateProfileBanner(action: any) {
  const {
    meta: { onFailure, onSuccess },
    payload: { file, identity, position, tempFile }
  } = action;

  try {
    const item = yield* getItem(identity);

    if (!item) return;

    const config = yield* getItemActionConfig(item, 'updateProfileCover');
    const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

    if (!config?.apiUrl) return;

    const formData = new FormData();

    if (tempFile) {
      formData.append('temp_file', tempFile?.temp_file);
    } else if (file) {
      formData.append('image', file);
    }

    formData.append('position', position.y.toString());

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: 'post',
      data: formData
    });

    const data = res?.data?.data;

    if (position) {
      yield* patchEntity(item._identity, {
        image_position: position.y.toString()
      });
    }

    if (file) {
      const result = normalization.normalize(data?.user);
      yield* fulfillEntity(result.data);
    }

    if (file && data?.feed_id && !data.is_pending) {
      yield* updateFeedItem(data?.feed_id, data.user, true);
    }

    if (file) {
      yield* makeDirtyPaging('feed');
    }

    yield* handleActionFeedback(res);
    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    isFunction(onFailure) && onFailure(error);
    yield* handleActionError(error);
  }
}

export function* removeBanner({
  payload: { identity },
  meta
}: ItemLocalAction<any, any>) {
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  const config = yield* getItemActionConfig(item, 'removeProfileCover');
  const ok = yield* handleActionConfirm(config);

  if (!ok) {
    return;
  }

  try {
    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod
    });

    yield* patchEntity(identity, {
      image: null,
      image_position: null
    });

    yield* handleActionFeedback(res);
    isFunction(meta.onSuccess) && meta.onSuccess();
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('@updatedItem/event', updatedEvent),
  takeEvery('event/hover', eventActive),
  takeEvery('event/updateProfileBanner', updateProfileBanner),
  takeEvery('event/removeBanner', removeBanner)
];

export default sagas;
