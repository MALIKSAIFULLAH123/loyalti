/**
 * @type: saga
 * name: forum_post.saga.editItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction,
  fulfillEntity,
  PAGINATION_PUSH
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import qs from 'querystring';

function* editPost({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { compactUrl, dialogBackend } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'editItem');

  if (!config?.apiUrl) return;

  try {
    yield dialogBackend.present({
      component: 'forum.dialog.FormDialog',
      props: {
        formUrl: compactUrl(config.apiUrl, item)
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* editedPost({
  payload
}: ItemLocalAction & {
  payload: { thread_id: string; quote_id?: number; thread?: any };
}) {
  if (!payload) return;

  const { normalization, compactData, getPageParams } =
    yield* getGlobalContext();
  const { page } = getPageParams();
  const { thread_id, quote_id } = payload;

  const result = normalization.normalize(payload);

  yield* fulfillEntity(result.data);

  // case submit or quote new post
  if (thread_id || quote_id) {
    const { apiUrl, apiParams = 'thread_id=:id' } = yield* getItemActionConfig(
      { resource_name: 'forum_post', module_name: 'forum' },
      'viewAll'
    );

    const pagingId = `${apiUrl.replace(/^\/?/, '/')}?${qs.stringify(
      compactData(apiParams, { id: payload?.thread?.id || thread_id })
    )}`;
    const pagingItem = yield* getItem(`pagination.${pagingId}`);

    if (!pagingItem) return;

    const pagingPage = pagingItem?.page;

    yield put({
      type: PAGINATION_PUSH,
      payload: {
        pagingId,
        currentPage: page || pagingPage || 1,
        data: result.ids
      }
    });

    return;
  }
}

const sagas = [
  takeEvery('forum_post/editItem', editPost),
  takeEvery('@updatedItem/forum_post', editedPost)
];

export default sagas;
