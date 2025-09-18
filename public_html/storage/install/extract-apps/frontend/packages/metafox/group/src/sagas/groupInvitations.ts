/**
 * @type: saga
 * name: saga.group.invitations
 */

import {
  AppResourceAction,
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getResourceAction,
  getSession,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  makeDirtyPaging,
  PAGINATION_REFRESH,
  patchEntity
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import qs from 'querystring';
import { call, put, takeEvery, takeLatest } from 'redux-saga/effects';
import { APP_GROUP, GROUP_MEMBER, GROUP_REQUEST, MEMBERSHIP } from '..';

export function* joinGroup(
  action: ItemLocalAction<{ onSuccess: any; identity: string }>
) {
  const {
    payload: { identity, onSuccess }
  } = action;

  const item = yield* getItem(identity);

  if (!item) return;

  if (item.resource_name !== APP_GROUP) return;

  const { membership: value } = item;

  const { apiClient, compactUrl, dispatch } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'joinGroup');

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: { group_id: item.id }
    });
    const { data, is_rule_confirmation } = response?.data?.data;

    data &&
      (yield put({
        type: 'updateMembershipQuestion/SUCCESS',
        payload: data
      }));

    is_rule_confirmation &&
      (yield put({
        type: 'group/showMembershipDialog',
        payload: { identity }
      }));

    if (data.membership === MEMBERSHIP) {
      dispatch({
        type: PAGINATION_REFRESH,
        payload: { pagingId: '/user/shortcut' }
      });
    }

    item?._loadedDetail && (yield* makeDirtyPaging('group'));

    if (onSuccess) onSuccess();

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { membership: value });
  }
}

export function* unjoinGroup(
  action: ItemLocalAction<{ onSuccess: any; identity: string }>
) {
  const {
    payload: { identity, onSuccess }
  } = action;
  let item = yield* getItem(identity);
  let groupId = identity;

  if (item && item.resource_name === GROUP_MEMBER) {
    groupId = `${APP_GROUP}.entities.group.${item?.group_id}`;
    item = yield* getItem(groupId);
  }

  if (!item) return;

  const { membership: value, is_owner, statistic } = item;

  const config = yield* getItemActionConfig(item, 'unjoinGroup');

  const { apiClient, compactUrl, i18n, redirectTo, dispatch } =
    yield* getGlobalContext();
  // will leave and delete group
  const isLeaveAndDel = statistic?.total_member === 1 && is_owner;
  let userAssign;

  const configConfirm = isLeaveAndDel
    ? {
        confirm: {
          title: i18n.formatMessage({
            id: 'confirm_title_leave_group_only_owner'
          }),
          message: i18n.formatMessage(
            {
              id: 'confirm_description_leave_group_only_owner'
            },
            { group_name: item?.title }
          )
        }
      }
    : config;
  const ok = yield* handleActionConfirm(configConfirm as AppResourceAction);

  if (!ok) {
    if (onSuccess) onSuccess();

    return;
  }

  if (!isLeaveAndDel && is_owner) {
    userAssign = yield call(handleOwnerLeaveGroup, groupId);

    if (!userAssign) return;
  }

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      params: compactData(config?.apiParams, {
        reassign_owner_id: userAssign?.user?.id
      })
    });
    const data = response?.data?.data;

    if (onSuccess) onSuccess();

    yield put({
      type: 'updateMembershipQuestion/SUCCESS',
      payload: data
    });

    item?._loadedDetail && (yield* makeDirtyPaging('group'));

    yield* handleActionFeedback(response);

    if (data?.redirect_url) {
      redirectTo(data?.redirect_url);
    }

    dispatch({
      type: PAGINATION_REFRESH,
      payload: { pagingId: '/user/shortcut' }
    });
  } catch (error) {
    yield* patchEntity(groupId, { membership: value });
  }
}

export function* handleOwnerLeaveGroup(identity: string) {
  const item = yield* getItem(identity);
  const { user: authUser } = yield* getSession();
  const { dialogBackend, i18n } = yield* getGlobalContext();

  if (!item || !item?.is_owner) return;

  if (item.statistic?.total_admin && item.statistic?.total_admin <= 1) {
    dialogBackend.alert({
      title: i18n.formatMessage({ id: 'alert' }),
      message: i18n.formatMessage(
        {
          id: 'you_are_the_last_admin_invite_or_select_new_admin_maintain_group'
        },
        { group_name: item?.title }
      )
    });

    return;
  }

  const apiUrlParams = compactData(
    { group_id: ':group_id', view: 'admin', excluded_user_id: ':authUserId' },
    {
      group_id: item.id,
      authUserId: authUser?.id
    }
  );

  const userAssign = yield dialogBackend.present({
    component: 'friend.dialog.FriendPicker',
    props: {
      dialogTitle: i18n.formatMessage({ id: 'select_an_admin_to_reassign' }),
      placeholder: i18n.formatMessage({ id: 'search_member' }),
      leftButton: false,
      apiUrl: `group-member?${qs.stringify(apiUrlParams)}`,
      itemView: 'group_member.ui.pickItem',
      chipLabel: 'user.full_name'
    }
  });

  return userAssign;
}

export function* inviteFriends(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const { dialogBackend, i18n, apiClient } = yield* getGlobalContext();

  try {
    if (!item) return;

    const { module_name, resource_name } = item;
    const { apiUrl, apiParams } = yield* getResourceAction(
      module_name,
      resource_name,
      'inviteFriends'
    );

    const values = yield dialogBackend.present({
      component: 'friend.dialog.MultipleFriendPicker',
      props: {
        dialogTitle: i18n.formatMessage({ id: 'invite_friends_to_this_group' }),
        leftButton: false,
        apiUrl,
        initialParams: compactData(apiParams, item)
      }
    });

    if (!values) return;

    const userIds = values.map(item => item.id);

    if (userIds.length === 0) return;

    const response = yield apiClient.request({
      method: 'post',
      url: '/group-invite',
      data: { group_id: item.id, user_ids: userIds }
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* acceptInvite(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  if (!item) return;

  if (item.resource_name !== APP_GROUP) return;

  const { apiClient, compactData, getPageParams } = yield* getGlobalContext();

  const pageParams = getPageParams();

  const config = yield* getItemActionConfig(item, 'acceptInvite');

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: config.apiUrl,
      params: compactData(config.apiParams, { id: item.id, ...pageParams })
    });

    const { data, is_rule_confirmation } = response?.data?.data;

    data &&
      (yield put({
        type: 'updateMembershipQuestion/SUCCESS',
        payload: data
      }));

    is_rule_confirmation &&
      (yield put({
        type: 'group/showMembershipDialog',
        payload: { identity }
      }));

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* declineInvite(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  if (!item) return;

  if (item.resource_name !== APP_GROUP) return;

  const { apiClient, compactData, redirectTo, getPageParams } =
    yield* getGlobalContext();

  const pageParams = getPageParams();

  const config = yield* getItemActionConfig(item, 'declineInvite');

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: config.apiUrl,
      params: compactData(config.apiParams, { id: item.id, ...pageParams })
    });

    const data = response?.data?.data;

    yield put({
      type: 'updateMembershipQuestion/SUCCESS',
      payload: data
    });

    yield* handleActionFeedback(response);

    if (data?.redirect_url) {
      redirectTo(data?.redirect_url);
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* cancelInvitation(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'cancelInvite');

  const user = yield* getItem(item.owner);
  const ok = yield* handleActionConfirm(config, { user });

  if (!ok) return;

  try {
    const user_id = item.owner.split('.')[3];

    const response = yield apiClient.request({
      method: config.apiMethod,
      url: config.apiUrl,
      params: compactData(config.apiParams, { ...item, user_id })
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* cancelRequest(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  if (!item) return;

  if (item.resource_name !== APP_GROUP) return;

  const { membership: value } = item;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(
    { module_name: APP_GROUP, resource_name: GROUP_REQUEST },
    'cancelRequest'
  );

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });
    const data = response?.data.data;

    yield put({
      type: 'updateMembershipQuestion/SUCCESS',
      payload: data
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { membership: value });
  }
}

const sagas = [
  takeLatest('group/join', joinGroup),
  takeEvery('group/unjoin', unjoinGroup),
  takeEvery('group/declineInvite', declineInvite),
  takeEvery('group/acceptInvite', acceptInvite),
  takeEvery('group/inviteFriends', inviteFriends),
  takeEvery('group/cancelInvitation', cancelInvitation),
  takeEvery('group/cancelRequest', cancelRequest)
];

export default sagas;
