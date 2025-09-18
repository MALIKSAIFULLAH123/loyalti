/**
 * @type: saga
 * name: group.saga.muteMember
 */

import {
  getGlobalContext,
  getItem,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  fulfillEntity,
  deleteEntity,
  getResourceAction,
  makeDirtyPaging
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_GROUP, GROUP_MUTE, GROUP_MEMBER } from '../constant';

function* unMuteMember({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { user: userIdentity } = item;

  const user = yield* getItem(userIdentity);

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_GROUP,
    GROUP_MUTE,
    'unmuteInGroup'
  );

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, { ...item, user_id: user?.id })
    });
    const status = response.data?.status;

    if (status === 'success') {
      if (item?.resource_name === GROUP_MUTE) {
        yield* makeDirtyPaging(GROUP_MEMBER);
        yield* deleteEntity(identity);
      } else {
        yield* makeDirtyPaging(GROUP_MUTE);
      }
    }

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

function* muteGroupWithForm({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { user: userIdentity } = item;
  const user = yield* getItem(userIdentity);
  const { dialogBackend, normalization } = yield* getGlobalContext();

  const dataSource = yield* getResourceAction(
    APP_GROUP,
    GROUP_MUTE,
    'muteInGroupForm'
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
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('group/unmuteInGroup', unMuteMember),
  takeEvery('group/muteInGroupForm', muteGroupWithForm)
];

export default sagas;
