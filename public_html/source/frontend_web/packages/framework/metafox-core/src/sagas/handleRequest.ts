/**
 * @type: saga
 * name: @core/request
 */

import { LocalAction, RemoteDataSource } from '@metafox/framework/types';
import { takeEvery } from 'redux-saga/effects';
import {
  getGlobalContext,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';

function* handleRequest(action: LocalAction<{ dataSource: RemoteDataSource }>) {
  const { apiUrl, apiMethod, apiParams } = action.payload.dataSource;

  try {
    const { apiClient } = yield* getGlobalContext();
    const response = yield apiClient.request({
      url: apiUrl,
      method: apiMethod,
      params: apiParams
    });
    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagas = [takeEvery('@core/request', handleRequest)];

export default sagas;
