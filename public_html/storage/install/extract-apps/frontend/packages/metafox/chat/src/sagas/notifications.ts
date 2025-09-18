/**
 * @type: saga
 * name: chat.saga.notifications
 */

import {
  getGlobalContext,
  getResourceAction,
  ItemLocalAction
} from '@metafox/framework';
import { takeLatest, put } from 'redux-saga/effects';
import { APP_CHAT, RESOURCE_ROOM } from '../constants';

function* getNotification() {
  const { apiClient } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_CHAT,
    RESOURCE_ROOM,
    'getNotification'
  );

  if (!config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'GET',
      url: config?.apiUrl
    });

    const data = response?.data?.data;

    yield put({
      type: 'chat/notifications/updateUnread',
      payload: { total_notification: data?.total_notification }
    });
  } catch (error) {
    yield;
  }
}

function* clearNotification() {
  const { apiClient } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_CHAT,
    RESOURCE_ROOM,
    'markAsSeenNotification'
  );

  if (!config?.apiUrl) return;

  try {
    yield apiClient.request({
      method: config?.apiMethod || 'POST',
      url: config?.apiUrl
    });

    yield put({
      type: 'chat/notifications/updateUnread',
      payload: { total_notification: 0 }
    });
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

function* updateNotification(
  action: ItemLocalAction<{ total_notification?: string }>
) {
  const data = action.payload;

  try {
    yield put({
      type: 'core/status/fulfill',
      payload: { chat_message: data?.total_notification || 0 }
    });
  } catch (error) {
    yield;
  }
}

const sagas = [
  takeLatest('chat/getNotification', getNotification),
  takeLatest('chat/notifications/clearUnread', clearNotification),
  takeLatest('chat/notifications/updateUnread', updateNotification)
];

export default sagas;
