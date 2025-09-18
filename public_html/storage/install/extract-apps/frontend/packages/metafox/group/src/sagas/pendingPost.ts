/**
 * @type: saga
 * name: saga.group.pendingPost
 */

import {
  getGlobalContext,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { compactData, compactUrl } from '@metafox/utils';
import { takeEvery } from 'redux-saga/effects';
import { APP_GROUP } from '..';

function* updatePendingMode(
  action: ItemLocalAction & {
    payload: { identity: string; pending_mode: boolean };
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { identity, pending_mode },
    meta: { onSuccess, onFailure }
  } = action;
  const { apiClient } = yield* getGlobalContext();

  try {
    const dataSource = yield* getItemActionConfig(
      { module_name: APP_GROUP, resource_name: APP_GROUP },
      'updatePendingMode'
    );

    const { apiUrl, apiMethod, apiParams } = dataSource;

    const id = identity.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: compactUrl(apiUrl, { id }),
      params: compactData(apiParams, { pending_mode: +pending_mode })
    });

    yield* patchEntity(identity, response?.data.data);

    yield* handleActionFeedback(response);
    typeof onSuccess === 'function' && onSuccess();
  } catch (error) {
    yield* handleActionError(error);
    typeof onFailure === 'function' && onFailure();
  }
}

const sagas = [takeEvery('group/updatePendingMode', updatePendingMode)];

export default sagas;
