/**
 * @type: saga
 * name: settings.invisibleSettings
 */
import {
  getGlobalContext,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';
import { put, takeEvery, takeLatest } from 'redux-saga/effects';

const apiUrl = '/account/invisible';

export function* fetchData() {
  const { apiClient } = yield* getGlobalContext();
  const response = yield apiClient.get(apiUrl);
  const payload = response.data?.data;

  if (payload) {
    yield put({ type: 'setting/invisibleSettings/FULFILL', payload });
  }
}

export function* updateData(action: any) {
  const { apiClient } = yield* getGlobalContext();

  try {
    const response = yield apiClient.put(apiUrl, { invisible: action.payload });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const effects = [
  takeLatest('setting/invisibleSettings/fetch', fetchData),
  takeEvery('setting/invisibleSettings/update', updateData)
];

export default effects;
