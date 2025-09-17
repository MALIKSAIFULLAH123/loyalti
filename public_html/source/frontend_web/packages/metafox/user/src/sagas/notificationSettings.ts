/**
 * @type: saga
 * name: settings.notificationSettings
 */
import {
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  getResourceAction
} from '@metafox/framework';
import { put, takeEvery, takeLatest } from 'redux-saga/effects';
import { APP_USER } from '@metafox/user/constant';

export function* fetchData() {
  const { apiClient } = yield* getGlobalContext();
  const { apiUrl, apiParams, apiMethod } = yield* getResourceAction(
    APP_USER,
    APP_USER,
    'getNotificationSettings'
  );

  try {
    const response = yield apiClient.request({
      url: apiUrl,
      method: apiMethod,
      params: apiParams
    });
    const payload = response.data?.data;

    if (payload) {
      yield put({ type: 'setting/notificationSettings/FULFILL', payload });
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* updateData(action: {
  type: string;
  payload: { var_name: string; value: number; channel: string; module_id };
}) {
  const { apiUrl, apiMethod } = yield* getResourceAction(
    APP_USER,
    APP_USER,
    'updateNotificationSettings'
  );

  try {
    const { var_name, value, channel, module_id } = action.payload;
    const { apiClient } = yield* getGlobalContext();

    const response = yield apiClient.request({
      url: apiUrl,
      method: apiMethod,
      data: { var_name, value, channel, module_id }
    });

    const payload = response.data?.data;

    if (payload) {
      yield put({ type: 'setting/notificationSettings/FULFILL', payload });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const effects = [
  takeLatest('setting/notificationSettings/FETCH', fetchData),
  takeEvery('setting/notificationSettings/UPDATE', updateData)
];

export default effects;
