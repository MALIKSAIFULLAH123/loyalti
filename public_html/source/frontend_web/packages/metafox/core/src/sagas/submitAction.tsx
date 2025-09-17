/**
 * @type: saga
 * name: core.submitAction
 */

import {
  FORM_SUBMIT_ACTION,
  getGlobalContext,
  getResourceAction,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* submitAction(
  action: ItemLocalAction & {
    payload: {
      module_name?: string;
      resource_name?: string;
      action_name?: string;
      values?: any;
    };
  }
) {
  const { apiClient, compactData } = yield* getGlobalContext();

  const { module_name, resource_name, action_name, values } = action.payload;

  const config = yield* getResourceAction(
    module_name,
    resource_name,
    action_name
  );

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config, values);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'PATCH',
      url: config.apiUrl,
      data: compactData(config.apiParams, values)
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery(FORM_SUBMIT_ACTION, submitAction)];

export default sagas;
