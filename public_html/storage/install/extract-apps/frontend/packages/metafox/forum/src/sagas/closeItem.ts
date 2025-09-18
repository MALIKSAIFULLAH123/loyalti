/**
 * @type: saga
 * name: forum_thread.saga.closeItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity,
  PAGINATION_REFRESH
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

function* toggleOpenItem({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { is_closed } = item;

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(
    item,
    is_closed ? 'reopenItem' : 'closeItem'
  );

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
      yield* patchEntity(identity, { is_closed: data?.is_closed });
    }

    yield* handleActionFeedback(response);
    yield put({
      type: PAGINATION_REFRESH,
      payload: {
        pagingId: `/forum-post?thread_id=${item.id}`
      }
    });

    return true;
  } catch (error) {
    yield* patchEntity(identity, { is_closed });
    yield* handleActionError(error);
  }

  return false;
}

const sagas = [
  takeEvery('forum/closeItem', toggleOpenItem),
  takeEvery('forum/reopenItem', toggleOpenItem)
];

export default sagas;
