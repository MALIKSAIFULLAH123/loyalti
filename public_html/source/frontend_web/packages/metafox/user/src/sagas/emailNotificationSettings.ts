/**
 * @type: saga
 * name: settings.emailNotificationSettings
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
    'getEmailNotificationSettings'
  );

  try {
    const response = yield apiClient.request({
      url: apiUrl,
      method: apiMethod,
      params: apiParams
    });
    const payload = response.data?.data;

    if (payload) {
      yield put({ type: 'setting/emailNotificationSettings/FULFILL', payload });
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
    'updateEmailNotificationSettings'
  );

  try {
    const { var_name, value, channel, module_id } = action.payload;
    const { apiClient } = yield* getGlobalContext();

    const response = yield apiClient.request({
      url: apiUrl,
      method: apiMethod,
      data: { var_name, value, channel, module_id }
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const effects = [
  takeLatest('setting/emailNotificationSettings/FETCH', fetchData),
  takeEvery('setting/emailNotificationSettings/UPDATE', updateData)
];

export default effects;
