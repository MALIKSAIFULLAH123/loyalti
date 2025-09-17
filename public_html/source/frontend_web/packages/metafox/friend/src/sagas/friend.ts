/**
 * @type: saga
 * name: friend
 */
import { getGlobalContext, getResourceAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* markAllAsRead() {
  const { apiClient } = yield* getGlobalContext();

  const { apiUrl, apiMethod = 'post' } = yield* getResourceAction(
    'friend',
    'friend_request',
    'markAllAsRead'
  );

  try {
    yield apiClient.request({
      method: apiMethod,
      url: apiUrl
    });
  } catch (err) {
    // err
  }
}

const sagas = [takeEvery('core/status/clearFriend', markAllAsRead)];

export default sagas;
