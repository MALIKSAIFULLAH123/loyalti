/**
 * @type: saga
 * name: requestAction
 */
import { takeLatest } from 'redux-saga/effects';
import {
  getGlobalContext,
  getResourceAction,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';

function* requestAction({
  payload
}: {
  type: 'confirmNextAction';
  payload: {
    action: {
      module_name: string;
      resource_name: string;
      action: string;
    };
  };
}) {
  const { module_name, resource_name, action } = payload.action;

  const config = yield* getResourceAction(module_name, resource_name, action);

  if (!config) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {

    const { apiClient } = yield* getGlobalContext();
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: config.apiUrl
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeLatest('@requestAction', requestAction)];

export default sagas;
