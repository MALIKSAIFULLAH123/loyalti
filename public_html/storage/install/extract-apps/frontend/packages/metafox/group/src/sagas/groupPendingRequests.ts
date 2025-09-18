/**
 * @type: saga
 * name: saga.group.pendingRequest
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  fulfillEntity,
  patchEntity
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeEvery } from 'redux-saga/effects';
import { APP_GROUP, GROUP_REQUEST } from '..';

function* declinePendingRequest(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const { dialogBackend } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  const dataSource = yield* getItemActionConfig(
    { module_name: APP_GROUP, resource_name: GROUP_REQUEST },
    'getDeclineRequestForm'
  );

  if (!item || !dataSource) return null;

  try {
    const response = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { ...item }
      }
    });

    const { group_id } = item;

    const statistic = response?.group?.statistic;

    if (statistic) {
      yield* patchEntity(`${APP_GROUP}.entities.${APP_GROUP}.${group_id}`, {
        statistic
      });
    }

    if (response) {
      yield* deleteEntity(identity);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* denyMemberRequest(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const { apiClient, normalization } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(
    { module_name: APP_GROUP, resource_name: GROUP_REQUEST },
    'denyMemberRequest'
  );

  const { apiUrl, apiMethod, apiParams } = dataSource;

  if (!identity) return null;

  const item = yield* getItem(identity);

  if (!item) return null;

  try {
    const { group_id, user } = item;
    const userId = user.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, { user_id: userId, group_id })
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* approvePendingRequest(
  action: ItemLocalAction & {
    payload: Record<string, any>;
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { identity }
  } = action;

  if (!identity) return null;

  const item = yield* getItem(identity);

  if (!item) return null;

  const { apiClient } = yield* getGlobalContext();
  const { apiUrl, apiMethod, apiParams } = yield* getItemActionConfig(
    { module_name: APP_GROUP, resource_name: GROUP_REQUEST },
    'acceptMemberRequest'
  );

  try {
    const { group_id, user } = item;
    const userId = user.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, { user_id: userId, group_id })
    });

    const statistic = response?.data?.data?.group?.statistic;

    if (statistic) {
      yield* patchEntity(`${APP_GROUP}.entities.${APP_GROUP}.${group_id}`, {
        statistic
      });
    }

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* acceptMemberRequest(
  action: ItemLocalAction & {
    payload: Record<string, any>;
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { identity }
  } = action;

  if (!identity) return null;

  const item = yield* getItem(identity);

  if (!item) return null;

  const { apiClient, normalization } = yield* getGlobalContext();
  const { apiUrl, apiMethod, apiParams } = yield* getItemActionConfig(
    { module_name: APP_GROUP, resource_name: GROUP_REQUEST },
    'acceptMemberRequest'
  );

  try {
    const { group_id, user } = item;
    const userId = user.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, { user_id: userId, group_id })
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('group/declinePendingRequest', declinePendingRequest),
  takeEvery('group/approvePendingRequest', approvePendingRequest),
  takeEvery('group/denyMemberRequest', denyMemberRequest),
  takeEvery('group/acceptMemberRequest', acceptMemberRequest)
];

export default sagas;
