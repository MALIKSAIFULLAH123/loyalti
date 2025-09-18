/**
 * @type: saga
 * name: group.saga.generateLinkInvite
 */
import {
  getGlobalContext,
  getItem,
  getResourceAction,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeEvery } from 'redux-saga/effects';
import { APP_GROUP, RESOURCE_GROUP } from '../constant';

export function* generateLinkInvite(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  try {
    yield dialogBackend.present({
      component: 'group.dialog.generateInviteLinkDialog',
      props: {
        item
      }
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* getCode(
  action: ItemLocalAction<{ item: any }, { onSuccess: (value) => void }>
) {
  const {
    payload: { item }
  } = action;
  const { apiClient } = yield* getGlobalContext();

  if (!item) return;

  try {
    const config = yield* getResourceAction(
      APP_GROUP,
      RESOURCE_GROUP,
      'generateInviteLink'
    );
    const apiUrl = config?.apiUrl;

    if (!apiUrl) return;

    const response = yield apiClient.request({
      url: apiUrl,
      method: config?.apiMethod || 'POST',
      params: compactData(config.apiParams, item)
    });

    const result = response.data.data;

    if (result) {
      action?.meta?.onSuccess && action?.meta?.onSuccess(result);
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}
export function* refreshCode(
  action: ItemLocalAction<{ item: any }, { onSuccess: (value) => void }>
) {
  const {
    payload: { item }
  } = action;
  const { apiClient } = yield* getGlobalContext();

  if (!item) return;

  try {
    const config = yield* getResourceAction(
      APP_GROUP,
      'group_invite_code',
      'refreshCode'
    );
    const apiUrl = config?.apiUrl;

    if (!apiUrl) return;

    const response = yield apiClient.request({
      url: apiUrl,
      method: config?.apiMethod || 'POST',
      params: compactData(config.apiParams, item)
    });

    const result = response.data.data;

    if (result) {
      action?.meta?.onSuccess && action?.meta?.onSuccess(result);
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagaEffect = [
  takeEvery('group/generateInviteLink', generateLinkInvite),
  takeEvery('group/getCode', getCode),
  takeEvery('group/refreshCode', refreshCode)
];

export default sagaEffect;
