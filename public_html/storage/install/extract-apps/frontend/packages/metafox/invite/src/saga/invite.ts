/**
 * @type: saga
 * name: invite
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getResourceAction,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { takeLatest, all, call } from 'redux-saga/effects';
import { APP_INVITE, RESOURCE_INVITE } from '../constants';

export function* resendItem({ payload }: ItemLocalAction) {
  const { identity } = payload;

  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(item, 'resend');

  try {
    const response = yield apiClient.request({
      method: dataSource.apiMethod,
      url: compactUrl(dataSource.apiUrl, { id: item?.id })
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* resendSelectAll({ payload, meta }: any) {
  const { ids } = payload;

  const { apiClient, compactData } = yield* getGlobalContext();

  const dataSource = yield* getResourceAction(
    APP_INVITE,
    RESOURCE_INVITE,
    'batchResend'
  );

  if (!dataSource || !ids.length) return;

  try {
    const response = yield apiClient.request({
      method: dataSource.apiMethod,
      url: dataSource.apiUrl,
      data: compactData(dataSource.apiParams, { ids })
    });

    meta?.onSuccess && meta?.onSuccess();
    yield* handleActionFeedback(response);
  } catch (error) {
    meta?.onFailure && meta?.onFailure();
    yield* handleActionError(error);
  }
}

export function* deleteAllItem({ payload, meta }: any) {
  const { ids } = payload;

  const { apiClient, compactData } = yield* getGlobalContext();

  const dataSource = yield* getResourceAction(
    APP_INVITE,
    RESOURCE_INVITE,
    'batchDeleted'
  );

  if (!dataSource || !ids.length) return;

  try {
    const response = yield apiClient.request({
      method: dataSource.apiMethod,
      url: dataSource.apiUrl,
      data: compactData(dataSource.apiParams, { ids })
    });

    yield all(
      ids.map(id => call(deleteEntity, `invite.entities.invite.${id}`))
    );

    meta?.onSuccess && meta?.onSuccess();

    yield* handleActionFeedback(response);
  } catch (error) {
    meta?.onFailure && meta?.onFailure();
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('resend', resendItem),
  takeLatest('resend/selectAll', resendSelectAll),
  takeLatest('deleteItem/selectAll', deleteAllItem)
];

export default sagas;
