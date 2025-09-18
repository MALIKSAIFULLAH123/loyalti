/**
 * @type: saga
 * name: saga.pages.managePage
 */
import {
  getGlobalContext,
  getItem,
  ItemLocalAction,
  getItemActionConfig,
  handleActionFeedback,
  handleActionError,
  deleteEntity,
  handleActionConfirm,
  fulfillEntity,
  makeDirtyPaging,
  getSession,
  getResourceAction
} from '@metafox/framework';
import { takeLatest, takeEvery } from 'redux-saga/effects';
import { APP_PAGE, RESOURCE_INVITE, RESOURCE_PAGE_MEMBER } from '../constant';
import qs from 'querystring';

export function* managePage(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const { navigate } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  if (!item) return;

  navigate(`/${APP_PAGE}/settings/${item.id}`);
}

export function* reassignOwner(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactData, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_PAGE_MEMBER, module_name: APP_PAGE },
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
        page_id: item.page_id,
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
  const { dialogBackend, i18n, apiClient, compactData } =
    yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_PAGE_MEMBER, module_name: APP_PAGE },
    'reassignOwner'
  );

  if (!item) return;

  if (item.statistic?.total_admin && item.statistic?.total_admin <= 1) {
    dialogBackend.alert({
      title: i18n.formatMessage({ id: 'select_an_admin_to_reassign' }),
      message: i18n.formatMessage(
        {
          id: 'there_are_no_admins_to_reassign_as_an_owner_please_invite_someone_to_become_new_admins_first'
        },
        { page_name: item?.title }
      )
    });

    return;
  }

  const apiUrlParams = compactData(
    { ...config.apiParams, view: 'admin', excluded_user_id: ':authUserId' },
    {
      page_id: item.id,
      authUserId: authUser?.id
    }
  );

  const values = yield dialogBackend.present({
    component: 'friend.dialog.FriendPicker',
    props: {
      dialogTitle: i18n.formatMessage({
        id: 'select_an_admin_to_reassign'
      }),
      placeholder: i18n.formatMessage({ id: 'search_an_admin' }),
      leftButton: false,
      apiUrl: `page-member?${qs.stringify(apiUrlParams)}`,
      itemView: 'page_member.ui.pickItem',
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
        page_id: item.id,
        user_id: values.user.id
      })
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* approvePendingPage(
  action: ItemLocalAction & { payload: { keepEntity: boolean } }
) {
  const {
    payload: { identity, keepEntity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: APP_PAGE, module_name: APP_PAGE },
    'approveItem'
  );

  if (!item) return;

  try {
    const { apiMethod, apiUrl } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: compactUrl(apiUrl, { id: item.id })
    });

    yield* handleActionFeedback(response);
    !keepEntity && (yield* deleteEntity(identity));
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* addAsAdmin(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { apiClient, i18n, normalization, compactData } =
    yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_PAGE_MEMBER, module_name: APP_PAGE },
    'addPageAdmins'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage(
        {
          id: 'invite_user_to_be_an_admin_page'
        },
        {
          user: user?.full_name
        }
      ),
      message: i18n.formatMessage(
        {
          id: 'invite_user_to_be_an_admin_description_page'
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
        id: item.page_id,
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

export function* removeMember(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { dialogBackend } = yield* getGlobalContext();
  const dataSource = yield* getItemActionConfig(
    { resource_name: RESOURCE_PAGE_MEMBER, module_name: APP_PAGE },
    'removeMember'
  );

  if (!item || !dataSource) return;

  const user = yield* getItem(item.user);

  const ok = yield handleActionConfirm(dataSource);

  if (!ok) return;

  try {
    const response = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { ...item, user_id: user?.id }
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

export function* removeAsAdmin(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactData } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_PAGE_MEMBER, module_name: APP_PAGE },
    'removeAsAdmin'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        page_id: item.page_id,
        user_id: user.id
      })
    });
    yield* makeDirtyPaging('page-member', undefined, false);

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
  const { apiClient, normalization, i18n, compactData } =
    yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_PAGE_MEMBER, module_name: APP_PAGE },
    'cancelAdminInvite'
  );

  if (!item) return;

  const user = yield* getItem(item?.user);

  const configConfirm = {
    confirm: {
      title: i18n.formatMessage({
        id: 'cancel_invite_user_to_be_an_admin_page'
      }),
      message: i18n.formatMessage(
        {
          id: 'cancel_invite_user_to_be_an_admin_description_page'
        },
        {
          user: user?.full_name
        }
      )
    }
  };

  const ok = yield* handleActionConfirm(configConfirm);

  if (!ok) return;

  try {
    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        page_id: item.page_id,
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

function* blockMember({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const user = yield* getItem(item.user);

  const { dialogBackend } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(item, 'blockFromPage');

  if (!dataSource?.apiUrl) return;

  const ok = yield* handleActionConfirm(dataSource);

  if (!ok) return false;

  try {
    const response = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { ...item, user_id: user?.id }
      }
    });

    if (response) {
      yield* deleteEntity(identity);
    }

    yield* makeDirtyPaging('page-block');

    yield* handleActionFeedback(response);

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

  const config = yield* getItemActionConfig(item, 'unblockFromPage');

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

    yield* makeDirtyPaging('page-member');

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

export function* addNewAdmin(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { dialogBackend, i18n, apiClient, compactData } =
    yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_PAGE_MEMBER, module_name: APP_PAGE },
    'addPageAdmins'
  );

  if (!item) return;

  const values = yield dialogBackend.present({
    component: 'friend.dialog.MultipleFriendPicker',
    props: {
      dialogTitle: i18n.formatMessage({ id: 'add_new_admin' }),
      placeholder: i18n.formatMessage({ id: 'search_member' }),
      leftButton: false,
      apiUrl: `page-member?page_id=${item.id}&view=member&not_invite_role=1`,
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

export function* cancelInvitation(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const owner = yield* getItem(item?.owner);

  if (!item) return;

  const { apiClient, compactData, compactUrl } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_PAGE,
    RESOURCE_INVITE,
    'cancelInvite'
  );

  if (!config?.apiUrl) return;

  const ok = yield handleActionConfirm(config, { user: owner });

  if (!ok) return;

  try {
    const user_id = item?.owner.split('.')[3];

    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      params: compactData(config.apiParams, { ...item, user_id })
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('pages/manage', managePage),
  takeEvery('page/reassignOwner', reassignOwner),
  takeEvery('page/reassignOwnerDialog', reassignOwnerDialog),
  takeEvery('page/approvePendingPage', approvePendingPage),
  takeEvery('page/setAsAdmin', addAsAdmin),
  takeEvery('page/removeMember', removeMember),
  takeEvery('page/removeAsAdmin', removeAsAdmin),
  takeEvery('page/removeAdminInvite', cancelAdminInvite),
  takeEvery('page/blockFromPage', blockMember),
  takeEvery('page/unblockFromPage', unblockMember),
  takeEvery('page/addNewAdmin', addNewAdmin),
  takeEvery('page/cancelInvitation', cancelInvitation)
];

export default sagas;
