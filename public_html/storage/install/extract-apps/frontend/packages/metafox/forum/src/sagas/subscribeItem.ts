/**
 * @type: saga
 * name: forum_thread.saga.subscribeItem
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

function* subscribeItem({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { is_subscribed } = item;

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'subscribeItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });

    if (response?.data) {
      const data = response.data?.data;
      yield* patchEntity(identity, { is_subscribed: data?.is_subscribed });
    }

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* patchEntity(identity, { is_subscribed });
    yield* handleActionError(error);
  }

  return false;
}

const sagas = [takeEvery('forum/subscribeItem', subscribeItem)];

export default sagas;
