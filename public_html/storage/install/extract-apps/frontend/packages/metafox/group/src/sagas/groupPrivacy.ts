/**
 * @type: saga
 * name: saga.group.privacy
 */
import {
  getGlobalContext,
  getResourceAction,
  handleActionError,
  handleActionFeedback,
  getItemActionConfig,
  handleActionConfirm,
  getItem
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { takeLatest } from 'redux-saga/effects';
import { APP_GROUP, RESOURCE_GROUP } from '../constant';

export function* updateData(action: any) {
  const remoteDataSource = yield* getResourceAction(
    APP_GROUP,
    RESOURCE_GROUP,
    'updateGroupPermission'
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

export function* cancelChangePrivacy(action: any) {
  const {
    payload: { id }
  } = action;
  const { onCancel } = action?.meta || {};
  const config = yield* getItemActionConfig(
    { module_name: APP_GROUP, resource_name: APP_GROUP },
    'cancelChangePrivacy'
  );
  const item = yield* getItem(`${APP_GROUP}.entities.group.${id}`);
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const ok = yield* handleActionConfirm(config, item);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, action.payload),
      method: config.apiMethod
    });

    onCancel && onCancel();

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('group/privacySetting/UPDATE', updateData),
  takeLatest('group/cancelChangePrivacy', cancelChangePrivacy)
];

export default sagas;
