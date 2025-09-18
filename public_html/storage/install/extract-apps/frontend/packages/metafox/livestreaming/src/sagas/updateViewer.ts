/**
 * @type: saga
 * name: livestreaming.saga.updateViewer
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  ItemLocalAction,
  getSession
} from '@metafox/framework';
import { takeLatest, delay } from 'redux-saga/effects';

let shouldPing = true;

function* updateViewer(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { user: authUser, loggedIn } = yield* getSession();
  const { user } = item || {};
  const isOwner = authUser?.id === user?.id;

  if (!item || !item?.is_streaming || isOwner || !loggedIn) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'updateViewer');
  const configPing = yield* getItemActionConfig(item, 'pingViewer');

  if (!config.apiUrl) return;

  try {
    shouldPing = true;
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    if (response) {
      while (shouldPing && configPing) {
        // 60s
        yield delay(60000);
        yield apiClient.request({
          method: configPing?.apiMethod || 'GET',
          url: compactUrl(configPing?.apiUrl, item)
        });
      }
    }
  } catch (error) {
    shouldPing = false;
  }
}

function* removeViewer(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { user: authUser, loggedIn } = yield* getSession();
  const { user } = item || {};
  const isOwner = authUser?.id === user?.id;

  if (!item || !item?.is_streaming || isOwner || !loggedIn) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'removeViewer');

  if (!config.apiUrl) return;

  try {
    shouldPing = false;
    yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });
  } catch (error) {}
}

const sagas = [
  takeLatest('livestreaming/updateViewer', updateViewer),
  takeLatest('livestreaming/removeViewer', removeViewer)
];

export default sagas;
