/**
 * @type: saga
 * name: shortcut.updateItem
 */
import {
  patchEntity,
  getGlobalContext,
  getItem,
  getResourceAction,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  PAGINATION_REFRESH
} from '@metafox/framework';
import { put, takeEvery } from 'redux-saga/effects';

function* reloadShortcut() {
  yield put({
    type: PAGINATION_REFRESH,
    payload: {
      apiUrl: '/user/shortcut',
      apiParams: {},
      pagingId: '/user/shortcut'
    }
  });
}

export function* autoSort(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getResourceAction('user', 'shortcut', 'updateItem');

  const sort_type = 1;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: { sort_type }
    });
    yield* handleActionFeedback(response);
    yield* patchEntity(identity, { sort_type });
    yield* reloadShortcut();
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* hideItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getResourceAction('user', 'shortcut', 'updateItem');

  const sort_type = 0;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: { sort_type }
    });
    yield* handleActionFeedback(response);
    yield* patchEntity(identity, { sort_type });
    yield* reloadShortcut();
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* pinItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getResourceAction('user', 'shortcut', 'updateItem');

  const sort_type = 2;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: { sort_type }
    });
    yield* handleActionFeedback(response);
    yield* patchEntity(identity, { sort_type });
    yield* reloadShortcut();
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('user/shortcut/sortAutomatically', autoSort),
  takeEvery('user/shortcut/hide', hideItem),
  takeEvery('user/shortcut/pinToTop', pinItem)
];

export default sagas;
