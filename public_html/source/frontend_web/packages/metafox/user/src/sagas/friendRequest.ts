/**
 * @type: saga
 * name: saga.friendRequest
 */
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  PAGINATION_DELETE,
  patchEntity,
  ENTITY_DELETE,
  fulfillEntity
} from '@metafox/framework';
import { FriendRequestItemShape } from '@metafox/friend';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_IS_FRIEND,
  FRIENDSHIP_REQUEST_SENT
} from '@metafox/friend/constant';
import { put, select, takeEvery } from 'redux-saga/effects';
import { getRequestFriendSelector } from '../selectors';
import qs from 'query-string';

export function* sendRequest(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getItemActionConfig(item, 'sendRequest');

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });
    const data = response?.data?.data;
    const { friendship } = data;

    yield* patchEntity(identity, {
      friendship: friendship || FRIENDSHIP_REQUEST_SENT
    });
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
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getItemActionConfig(item, 'cancelRequest');
  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });
    const data = response?.data?.data;
    const { friendship } = data;

    yield* patchEntity(identity, {
      friendship: friendship || FRIENDSHIP_CAN_ADD_FRIEND,
      extra: { ...item.extra, can_add_friend: true }
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* unfriend(action: ItemLocalAction) {
  const {
    payload: { identity },
    meta
  } = action;
  const { onFinally } = meta || {};
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, compactData, getPageParams } =
    yield* getGlobalContext();
  const { list_id } = getPageParams();

  if (!item) return null;

  const config = yield* getItemActionConfig(item, 'unfriend');
  const ok = yield* handleActionConfirm(config);

  if (!ok) {
    onFinally && onFinally();

    return;
  }

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });
    const data = response?.data?.data;
    const { friendship } = data;

    yield* patchEntity(identity, {
      friendship: friendship || FRIENDSHIP_CAN_ADD_FRIEND,
      extra: { ...item.extra, can_add_friend: true }
    });

    if (list_id) {
      yield put({
        type: ENTITY_DELETE,
        payload: {
          identity,
          pagingId: `friend?${qs.stringify({ list_id })}`
        }
      });
    }

    yield put({ type: 'friend/friend_list/deleteItem/SUCCESS' });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  } finally {
    onFinally && onFinally();
  }
}
export function* getFriendRequest(
  identity: string
): Generator<unknown, FriendRequestItemShape, unknown> {
  return (yield select(getRequestFriendSelector, identity)) as any;
}

export function* acceptUserFriendRequest(action: ItemLocalAction) {
  const {
    payload: { identity, idRequest }
  } = action;
  const user = yield* getItem(identity);
  const requestId = yield* getFriendRequest(identity);

  try {
    const { apiClient, compactUrl, normalization } = yield* getGlobalContext();

    const config = yield* getItemActionConfig(user, 'acceptFriendRequest');

    const response = yield apiClient.request({
      method: 'put',
      url: compactUrl(config.apiUrl, user),
      data: { action: 'approve' }
    });

    yield put({ type: PAGINATION_DELETE, payload: { identity: idRequest } });
    yield put({ type: 'core/status/decreaseRequest' });

    const data = response?.data?.data;

    const friendship = data?.friendship ?? FRIENDSHIP_IS_FRIEND;
    yield* handleActionFeedback(response);

    if (requestId) {
      yield* patchEntity(requestId._identity, {
        friendship
      });
    }

    if (data) {
      const data = response?.data?.data;

      const result = normalization.normalize(data);

      yield* fulfillEntity(result.data);
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* deleteUserFriendRequest(action: ItemLocalAction) {
  const {
    payload: { identity, idRequest }
  } = action;
  const user = yield* getItem(identity);
  const requestId = yield* getFriendRequest(identity);

  try {
    const { apiClient, compactUrl } = yield* getGlobalContext();
    const config = yield* getItemActionConfig(user, 'denyFriendRequest');

    const response = yield apiClient.request({
      method: 'put',
      url: compactUrl(config.apiUrl, user),
      params: { action: 'deny' }
    });

    yield put({ type: PAGINATION_DELETE, payload: { identity: idRequest } });
    yield put({ type: 'core/status/decreaseRequest' });

    const data = response?.data?.data;

    const friendship = data?.friendship ?? FRIENDSHIP_CAN_ADD_FRIEND;

    if (requestId) {
      yield* patchEntity(requestId._identity, {
        friendship
      });
    }

    yield* patchEntity(identity, {
      friendship
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* unfollowUser(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { extra } = item;
  const { apiClient, compactUrl } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getItemActionConfig(item, 'unfollow');

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });
    const data = response.data?.data?.extra || {};

    yield* patchEntity(identity, {
      is_following: false,
      extra: {
        ...extra,
        ...data
      }
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* followUser(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { extra } = item;
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getItemActionConfig(item, 'follow');

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, { user_id: item.id })
    });
    const data = response.data?.data?.extra;

    yield* patchEntity(identity, {
      is_following: true,
      extra: {
        ...extra,
        ...data
      }
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('user/addFriend', sendRequest),
  takeEvery('user/cancelRequest', cancelRequest),
  takeEvery('user/unFriend', unfriend),
  takeEvery('user/acceptFriendRequest', acceptUserFriendRequest),
  takeEvery('user/denyFriendRequest', deleteUserFriendRequest),
  takeEvery('user/unfollow', unfollowUser),
  takeEvery('user/follow', followUser)
];

export default sagas;
