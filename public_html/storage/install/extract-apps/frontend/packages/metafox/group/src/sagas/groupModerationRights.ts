/**
 * @type: saga
 * name: saga.group.moderationRights
 */
import {
  getGlobalContext,
  getResourceAction,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { takeLatest } from 'redux-saga/effects';
import { APP_GROUP, RESOURCE_GROUP } from '../constant';

export function* updateData(action: any) {
  const remoteDataSource = yield* getResourceAction(
    APP_GROUP,
    RESOURCE_GROUP,
    'updateModerationRight'
  );

  const { id, ...payload } = action.payload;

  try {
    const { apiUrl, apiMethod } = remoteDataSource;

    const { apiClient } = yield* getGlobalContext();

    const response = yield apiClient.request({
      url: compactUrl(apiUrl, { id: action.payload.id }),
      method: apiMethod,
      data: payload
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeLatest('group/moderationRights/UPDATE', updateData)];

export default sagas;
