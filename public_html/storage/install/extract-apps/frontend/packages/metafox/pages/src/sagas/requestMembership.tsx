/**
 * @type: saga
 * name: saga.requestMembership
 */

import {
  getGlobalContext,
  getItem,
  getResourceAction,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity,
  makeDirtyPaging,
  deleteEntity,
  PAGINATION_REFRESH
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeEvery, put } from 'redux-saga/effects';
import { APP_PAGE, RESOURCE_INVITE } from '../constant';

export function* likePage(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  if (!item) return;

  if (item.resource_name !== 'page') return;

  const { is_liked: value } = item;

  if (value) return; // is liked before

  const { apiClient, compactUrl, dispatch } = yield* getGlobalContext();
  const config = {
    apiUrl: '/page-member',
    apiMethod: 'post' as any
  };

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: { page_id: item.id }
    });

    yield* patchEntity(identity, response.data.data);

    dispatch({
      type: PAGINATION_REFRESH,
      payload: { pagingId: '/user/shortcut' }
    });
  } catch (err) {
    //
    yield* patchEntity(identity, { is_liked: value });

    yield* handleActionError(err);
  }
}

export function* unlikePage(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  if (!item) return;

  if (item.resource_name !== 'page') return;

  const { is_liked: value } = item;

  if (!value) return; // is liked before

  const { apiClient, compactUrl, dispatch } = yield* getGlobalContext();
  const config = {
    apiUrl: '/page-member/:id',
    apiMethod: 'delete' as any
  };

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    yield* patchEntity(identity, response.data.data);

    dispatch({
      type: PAGINATION_REFRESH,
      payload: { pagingId: '/user/shortcut' }
    });
  } catch (err) {
    yield* patchEntity(identity, { is_liked: value });
    yield* handleActionError(err);
  }
}

export function* inviteFriends(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const { dialogBackend, i18n, apiClient } = yield* getGlobalContext();

  if (!item) return;

  try {
    const { module_name, resource_name } = item;

    const { apiUrl, apiParams } = yield* getResourceAction(
      module_name,
      resource_name,
      'inviteFriends'
    );

    const values = yield dialogBackend.present({
      component: 'friend.dialog.MultipleFriendPicker',
      props: {
        dialogTitle: i18n.formatMessage({ id: 'invite_friends_to_this_page' }),
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
      url: '/page-invite',
      data: { page_id: item.id, user_ids: userIds }
    });

    yield* makeDirtyPaging('page-invite', null, false);

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

  if (item.resource_name !== APP_PAGE) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_PAGE,
    RESOURCE_INVITE,
    'acceptInvite'
  );

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      params: config.apiParams
    });

    yield* patchEntity(identity, response.data.data);
    yield* makeDirtyPaging('page-member');

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

  if (item.resource_name !== APP_PAGE) return;

  const { apiClient, compactData, redirectTo, compactUrl } =
    yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_PAGE,
    RESOURCE_INVITE,
    'declineInvite'
  );

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      params: compactData(config.apiParams, { id: item.id })
    });

    const data = response?.data?.data;

    yield* handleActionFeedback(response);

    if (data?.redirect_url) {
      redirectTo(data?.redirect_url);
    }

    yield* deleteEntity(item?.invite);

    yield put({
      type: PAGINATION_REFRESH,
      payload: { pagingId: 'page?view=invited' }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('page/like', likePage),
  takeEvery('page/unlike', unlikePage),
  takeEvery('page/inviteFriends', inviteFriends),
  takeEvery('page/acceptInvite', acceptInvite),
  takeEvery('page/declineInvite', declineInvite)
];

export default sagas;
