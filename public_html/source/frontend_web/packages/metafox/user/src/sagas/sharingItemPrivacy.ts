/**
 * @type: saga
 * name: settings.sharingItemPrivacy
 */
import {
  getGlobalContext,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { put, takeEvery, takeLatest } from 'redux-saga/effects';
import { APP_USER } from '../constant';
import { getAllSharingItemPrivacy } from './getSharingItemPrivacy';

export function* fetchDataPrivacy(action: { payload: { id: number } }) {
  const { payload } = action;
  const { apiClient } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(
    { resource_name: APP_USER, module_name: APP_USER },
    'getItemPrivacySettings'
  );

  const privacy = yield* getAllSharingItemPrivacy();

  if (!config || privacy.length) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, { id: payload.id }),
      method: config.apiMethod
    });

    const data = response.data?.data;

    if (data) {
      yield put({ type: 'setting/sharingItemPrivacy/FULFILL', payload: data });
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* updateData(action: {
  type: string;
  payload: { var_name: string; value: number };
}) {
  const { var_name, value } = action.payload;
  const { apiClient } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: APP_USER, module_name: APP_USER },
    'updateItemPrivacySettings'
  );

  if (!config) return;

  try {
    const response = yield apiClient.request({
      url: config.apiUrl,
      method: config.apiMethod,
      data: {
        [var_name]: value
      }
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const effects = [
  takeLatest('setting/sharingItemPrivacy/FETCH', fetchDataPrivacy),
  takeEvery('setting/sharingItemPrivacy/UPDATE', updateData)
];

export default effects;
