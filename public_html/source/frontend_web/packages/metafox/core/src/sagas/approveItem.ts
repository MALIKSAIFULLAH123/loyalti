/**
 * @type: saga
 * name: core.saga.approveItem
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
import deletePagination from '@metafox/framework/sagas/deletePagination';
import { takeEvery } from 'redux-saga/effects';

function* approveItem(
  action: ItemLocalAction<{ identity: string }, { onSuccess: any }>
) {
  const { identity } = action.payload;
  const { onSuccess } = action?.meta || {};
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

    yield* deletePagination(identity);
    yield* makeDirtyPaging(item.module_name, 'pending');
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('approveItem', approveItem)];

export default sagas;
