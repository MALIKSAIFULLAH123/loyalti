/**
 * @type: saga
 * name: forum_thread.saga.stickItem
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

function* stickItem({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { is_sticked } = item;

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'stickItem');

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
      yield* patchEntity(identity, { is_sticked: data?.is_sticked });
    }

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* patchEntity(identity, { is_sticked });
    yield* handleActionError(error);
  }

  return false;
}

const sagas = [takeEvery('forum/stickItem', stickItem)];

export default sagas;
