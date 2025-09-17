/**
 * @type: saga
 * name: user.verify
 */
import {
  getGlobalContext,
  getResourceAction,
  handleActionFeedback,
  LocalAction
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { get } from 'lodash';
import { takeLatest, put } from 'redux-saga/effects';

function* verification({ payload }: LocalAction<{ hash: string }>) {
  const { apiClient } = yield* getGlobalContext();

  try {
    const config = yield* getResourceAction('user', 'user_verify', 'verify');

    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, { hash: payload.hash })
    });

    yield put({
      type: 'user/verification/update',
      payload: {
        loading: false,
        success: true
      }
    });
    yield* handleActionFeedback(response);
  } catch (err) {
    const error = get(err, 'response.data.error');

    yield put({
      type: 'user/verification/update',
      payload: { loading: false, error }
    });
  }
}

const sagas = [takeLatest('user/verification', verification)];

export default sagas;
