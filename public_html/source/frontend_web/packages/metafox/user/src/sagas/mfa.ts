/**
 * @type: saga
 * name: user.authenticator_mfa
 */
import {
  ItemLocalAction,
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  getResourceAction
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

export function* handleAction({
  payload: { data, module_name, resource_name, action_name }
}: ItemLocalAction<{
  resource_name: string;
  module_name: string;
  action_name: string;
  data?: Record<string, any>;
}>) {
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const dataSource = yield* getResourceAction(
    module_name,
    resource_name,
    action_name
  );

  if (!dataSource) return;

  try {
    const response = yield apiClient.request({
      method: dataSource.apiMethod || 'GET',
      url: compactUrl(dataSource.apiUrl, data),
      data
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const effects = [takeLatest('authenticator/mfa/resend', handleAction)];

export default effects;
