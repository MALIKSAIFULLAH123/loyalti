/**
 * @type: saga
 * name: core.removeFeed
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

function* removeFeed({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'removeItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  const { module_name, resource_name } = item;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });
    yield* deleteEntity(identity);
    yield* handleActionFeedback(response);

    yield put({
      type: `${module_name}/${resource_name}/removeFeed/DONE`,
      payload: { ...payload, ...item, ...response.data.data }
    });
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

function* deleteWithItems({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'deleteWithItems');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  const { module_name, resource_name } = item;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'DELETE',
      url: compactUrl(config.apiUrl, item)
    });
    yield* deleteEntity(identity);
    yield* handleActionFeedback(response);

    yield put({
      type: `${module_name}/${resource_name}/deleteWithItems/DONE`,
      payload: { ...payload, ...item, ...response.data.data }
    });
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

const sagas = [
  takeEvery('feed/removeItem', removeFeed),
  takeEvery('feed/deleteWithItems', deleteWithItems)
];

export default sagas;
