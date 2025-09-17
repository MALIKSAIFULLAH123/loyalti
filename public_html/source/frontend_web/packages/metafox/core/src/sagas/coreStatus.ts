/**
 * @type: saga
 * name: saga.coreStatus
 */
import {
  APP_BOOTSTRAP_DONE,
  IS_INSTALLATION,
  getGlobalContext
} from '@metafox/framework';
import { delay, put, takeLatest } from 'redux-saga/effects';

function* coreStatus() {
  // DO NOT run status update if tab is not visible to safe resource.
  // https://www.w3.org/TR/page-visibility/
  if (document.hidden) {
    return;
  }

  const { apiClient, cookieBackend } = yield* getGlobalContext();

  if (!cookieBackend.get('token')) return;

  try {
    const response = yield apiClient.get('/core/status');

    yield put({ type: 'core/status/fulfill', payload: response.data?.data });

    // should compare status before fulfill.
  } catch (error) {
    // implicit error
  }
}

function* registerTask() {
  // todo read from config
  // should moved to getSetting soon.!

  const { getSetting } = yield* getGlobalContext();
  const refresh_time = getSetting<number>('notification.refresh_time');

  const delayTime = refresh_time * 60 * 1000;

  if (IS_INSTALLATION) {
    return;
  }

  // delay 5 seconds before push first call
  yield delay(5000);
  yield* coreStatus();

  while (delayTime) {
    yield delay(delayTime);
    yield* coreStatus();
  }
}

const sagas = [takeLatest(APP_BOOTSTRAP_DONE, registerTask)];

export default sagas;
