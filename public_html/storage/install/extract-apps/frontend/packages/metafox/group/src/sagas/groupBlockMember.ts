/**
 * @type: saga
 * name: group.saga.blockMember
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  deleteEntity,
  makeDirtyPaging,
  getResourceAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_GROUP, GROUP_MEMBER } from '../constant';

function* blockMember({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { user: userIdentity } = item;

  const user = yield* getItem(userIdentity);

  const { dialogBackend } = yield* getGlobalContext();

  const dataSource = yield* getResourceAction(
    APP_GROUP,
    GROUP_MEMBER,
    'blockFromGroup'
  );

  if (!dataSource?.apiUrl) return;

  try {
    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { ...item, user_id: user?.id }
      }
    });

    if (data) {
      yield* deleteEntity(identity);
    }

    yield* makeDirtyPaging('group-block');

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

function* unblockMember({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { user: userIdentity } = item;

  const user = yield* getItem(userIdentity);

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'unblockFromGroup');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config, { user });

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'post',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, { ...item, user_id: user?.id })
    });

    if (response?.data) {
      const status = response.data?.status;

      if (status === 'success') {
        yield* deleteEntity(identity);
      }
    }

    yield* makeDirtyPaging('group-member');

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

const sagas = [
  takeEvery('group/blockFromGroup', blockMember),
  takeEvery('group/unblockFromGroup', unblockMember)
];

export default sagas;
