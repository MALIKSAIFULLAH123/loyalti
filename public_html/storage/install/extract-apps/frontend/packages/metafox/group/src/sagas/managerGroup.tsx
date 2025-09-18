/**
 * @type: saga
 * name: saga.group.managerGroup
 */

import {
  AppResourceAction,
  deleteEntity,
  deletePagination,
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getSession,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { isArray } from 'lodash';
import qs from 'querystring';
import { takeEvery } from 'redux-saga/effects';
import {
  APP_GROUP,
  GROUP_QUESTION,
  GROUP_MEMBER
} from '@metafox/group/constant';

export function* manageGroup(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const { navigate } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  if (!item) return;

  navigate(`/group/manage/${item.id}`);
}

export function* reviewYourContent(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const { navigate } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  if (!item) return;

  navigate(`/group/${item.id}/review_my_content`);
}

export function* addNewAdmin(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { dialogBackend, i18n, apiClient, compactData } =
    yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'addGroupAdmins'
  );

  const configInvite = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'inviteGroupAdmin'
  );

  if (!item) return;

  const values = yield dialogBackend.present({
    component: 'friend.dialog.MultipleFriendPicker',
    props: {
      dialogTitle: i18n.formatMessage({ id: 'add_new_admin' }),
      placeholder: i18n.formatMessage({ id: 'search_members' }),
      leftButton: false,
      initialParams: compactData(configInvite.apiParams, { group_id: item.id }),
      apiUrl: configInvite.apiUrl,
      itemView: 'group_member.ui.pickItem',
      chipLabel: 'user.full_name'
    }
  });

  if (!values) return;

  const ids = values.map(item => item.user.id);

  if (ids.length === 0) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        id: item.id,
        ids
      })
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeAdmin(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'removeGroupAdmin'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* cancelAdminInvite(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, normalization, i18n } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'cancelAdminInvite'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage({
        id: 'cancel_invite_user_to_be_an_admin'
      }),
      message: i18n.formatMessage(
        {
          id: 'cancel_invite_user_to_be_an_admin_description'
        },
        {
          user: user?.full_name
        }
      )
    }
  };

  const ok = yield* handleActionConfirm(configConfirm as AppResourceAction);

  if (!ok) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);
    yield* fulfillEntity(result.data);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* changeToModerator(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { apiClient } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'changeToModerator'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeAsAdmin(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, i18n, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'removeAsAdmin'
  );

  const prefixPagingId = '/group-member-admin';

  if (!item) return;

  const user = yield* getItem(item?.user);
  const group = yield* getItem(`group.entities.group.${item?.group_id}`);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage({
        id: 'remove_group_admin'
      }),
      message: i18n.formatMessage(
        {
          id: 'you_are_about_to_remove_group_admin'
        },
        {
          userName: user?.full_name,
          groupName: group.title
        }
      )
    }
  };

  const ok = yield* handleActionConfirm(configConfirm as AppResourceAction);

  if (!ok) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
    yield* handleActionFeedback(response);

    yield* deletePagination(identity, prefixPagingId);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* addNewModerator(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { dialogBackend, i18n, apiClient, compactData } =
    yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'addGroupModerators'
  );

  const configInvite = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'inviteGroupModerator'
  );

  if (!item) return;

  const values = yield dialogBackend.present({
    component: 'friend.dialog.MultipleFriendPicker',
    props: {
      dialogTitle: i18n.formatMessage({ id: 'add_new_moderator' }),
      placeholder: i18n.formatMessage({ id: 'search_members' }),
      leftButton: false,
      initialParams: compactData(configInvite.apiParams, { group_id: item.id }),
      apiUrl: configInvite.apiUrl,
      itemView: 'group_member.ui.pickItem',
      chipLabel: 'user.full_name'
    }
  });

  if (!values) return;

  const ids = values.map(item => item.user.id);

  if (ids.length === 0) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        id: item.id,
        ids
      })
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* addAsModerator(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, i18n } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'addGroupModerators'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage(
        {
          id: 'invite_user_to_be_a_moderator'
        },
        {
          user: user?.full_name
        }
      ),
      message: i18n.formatMessage(
        {
          id: 'invite_user_to_be_a_moderator_description'
        },
        {
          user: user?.full_name
        }
      ),
      positiveButton: {
        label: i18n.formatMessage({
          id: 'invite'
        })
      }
    }
  };

  const ok = yield* handleActionConfirm(configConfirm as AppResourceAction);

  if (!ok) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        id: item.group_id,
        ids: [user.id]
      })
    });

    const data = response?.data?.data;

    isArray(data) &&
      data[0]?.extra &&
      (yield* patchEntity(identity, { extra: data[0]?.extra }));

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* addAsAdmin(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { apiClient, i18n, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'addGroupAdmins'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage(
        {
          id: 'invite_user_to_be_an_admin'
        },
        {
          user: user?.full_name
        }
      ),
      message: i18n.formatMessage(
        {
          id: 'invite_user_to_be_an_admin_description'
        },
        {
          user: user?.full_name
        }
      ),
      positiveButton: {
        label: i18n.formatMessage({
          id: 'invite'
        })
      }
    }
  };

  const ok = yield* handleActionConfirm(configConfirm as AppResourceAction);

  if (!ok) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        id: item.group_id,
        ids: [user.id]
      })
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeAsModerator(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, i18n, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'removeAsModerator'
  );

  const prefixPagingId = '/group-member-moderators';

  if (!item) return;

  const user = yield* getItem(item?.user);
  const group = yield* getItem(`group.entities.group.${item?.group_id}`);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage({
        id: 'remove_group_moderator'
      }),
      message: i18n.formatMessage(
        {
          id: 'you_are_about_to_remove_group_moderator'
        },
        {
          userName: user?.full_name,
          groupName: group.title
        }
      )
    }
  };

  const ok = yield* handleActionConfirm(configConfirm as AppResourceAction);

  if (!ok) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);

    yield* handleActionFeedback(response);
    yield* deletePagination(identity, prefixPagingId);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeModerator(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'removeGroupModerator'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });

    yield* deleteEntity(identity);
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* cancelModeratorInvite(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, i18n, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'cancelModeratorInvite'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage({
        id: 'cancel_invite_user_to_be_a_moderator'
      }),
      message: i18n.formatMessage(
        {
          id: 'cancel_invite_user_to_be_a_moderator_description'
        },
        {
          user: user?.full_name
        }
      )
    }
  };

  const ok = yield* handleActionConfirm(configConfirm as AppResourceAction);

  if (!ok) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });

    const data = response?.data?.data;

    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeMember(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();
  const dataSource = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'removeMember'
  );

  if (!item) return;

  const user = yield* getItem(item.user);
  const group = yield* getItem(`group.entities.group.${item.group_id}`);

  const ok = yield* handleActionConfirm(dataSource);

  if (!ok) return;

  try {
    const response = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { ...item, user_id: user?.id, group_id: group?.id }
      }
    });

    if (response) {
      yield* deleteEntity(identity);
      yield* handleActionFeedback(response);
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* reassignOwner(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'reassignOwner'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.group_id,
        user_id: user.id
      })
    });
    const data = response?.data?.data;

    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* reassignOwnerDialog(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { user: authUser } = yield* getSession();
  const { dialogBackend, i18n, apiClient } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_MEMBER, module_name: APP_GROUP },
    'reassignOwner'
  );

  if (!item) return;

  if (item.statistic?.total_admin && item.statistic?.total_admin <= 1) {
    dialogBackend.alert({
      title: i18n.formatMessage({ id: 'alert' }),
      message: i18n.formatMessage(
        {
          id: 'there_are_no_admins_to_reassign_as_an_owner_please_invite_someone_to_become_new_admins_first'
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
  const values = yield dialogBackend.present({
    component: 'friend.dialog.FriendPicker',
    props: {
      dialogTitle: i18n.formatMessage({ id: 'select_an_admin_to_reassign' }),
      placeholder: i18n.formatMessage({ id: 'search_an_admin' }),
      leftButton: false,
      apiUrl: `group-member?${qs.stringify(apiUrlParams)}`,
      itemView: 'group_member.ui.pickItem',
      chipLabel: 'user.full_name'
    }
  });

  if (!values?.user?.id) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        group_id: item.id,
        user_id: values.user.id
      })
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* viewMemberQuestions(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { dialogBackend, compactData } = yield* getGlobalContext();
  const dataSource = yield* getItemActionConfig(
    { module_name: APP_GROUP, resource_name: GROUP_QUESTION },
    'viewAnswers'
  );

  if (!dataSource) return;

  try {
    yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource: {
          apiUrl: dataSource.apiUrl,
          apiParams: compactData(dataSource.apiParams, item)
        }
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('group/manage', manageGroup),
  takeEvery('group/yourContent', reviewYourContent),
  takeEvery('group/addNewAdmin', addNewAdmin),
  takeEvery('group/cancelAdminInvite', cancelAdminInvite),
  takeEvery('group/removeAdmin', removeAdmin),
  takeEvery('group/addNewModerator', addNewModerator),
  takeEvery('group/changeToModerator', changeToModerator),
  takeEvery('group/removeAsAdmin', removeAsAdmin),
  takeEvery('group/removeAsModerator', removeAsModerator),
  takeEvery('group/cancelModeratorInvite', cancelModeratorInvite),
  takeEvery('group/removeModerator', removeModerator),
  takeEvery('group/addAsAdmin', addAsAdmin),
  takeEvery('group/setAsAdmin', addAsAdmin),
  takeEvery('group/setAsModerator', addAsModerator),
  takeEvery('group/removeMember', removeMember),
  takeEvery('group/reassignOwner', reassignOwner),
  takeEvery('group/reassignOwnerDialog', reassignOwnerDialog),
  takeEvery('group/viewMemberQuestions', viewMemberQuestions),
  takeEvery('group/viewAnswers', viewMemberQuestions)
];

export default sagas;
