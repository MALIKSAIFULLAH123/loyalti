/**
 * @type: saga
 * name: marketplace.saga.reopenItem
 */

import {
  patchEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  makeDirtyPaging
} from '@metafox/framework';
import deletePagination from '@metafox/framework/sagas/deletePagination';
import { takeEvery } from 'redux-saga/effects';

function* reopenItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'reopenItem');

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
      yield* patchEntity(identity, data);
    }

    yield* deletePagination(identity);
    yield* makeDirtyPaging(item.module_name, 'expire');
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('marketplace/reopenItem', reopenItem)];

export default sagas;
