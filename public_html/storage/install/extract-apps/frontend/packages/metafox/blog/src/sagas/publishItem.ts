/**
 * @type: saga
 * name: blog.saga.publishItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  deletePagination,
  fulfillEntity,
  makeDirtyPaging
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* publishItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'publishBlog');

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

    yield* deletePagination(identity);
    yield* makeDirtyPaging(item.module_name, 'draft');
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('publishBlog', publishItem)];

export default sagas;
