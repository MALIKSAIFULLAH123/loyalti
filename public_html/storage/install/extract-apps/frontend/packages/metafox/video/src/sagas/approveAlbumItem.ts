/**
 * @type: saga
 * name: video.saga.approveAlbumItem
 */

import {
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  makeDirtyPaging
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* approveItem(action: ItemLocalAction<{ onSuccess: any }>) {
  const { identity, onSuccess } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'approveItem');

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });
    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    if (onSuccess) onSuccess();

    yield* makeDirtyPaging(item.module_name, 'pending');
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('video/approveAlbumItem', approveItem)];

export default sagas;
