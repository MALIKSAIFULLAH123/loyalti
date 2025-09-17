/**
 * @type: saga
 * name: friend_request
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  PAGINATION_UN_LIST,
  patchEntity,
  deleteEntity
} from '@metafox/framework';
import { put, takeEvery } from 'redux-saga/effects';
import { FriendRequestItemShape } from '..';
import { FRIENDSHIP_CAN_ADD_FRIEND, FRIENDSHIP_IS_FRIEND } from '../constant';

export function* acceptFriendRequest({
  payload: { identity }
}: ItemLocalAction) {
  const { id: requestId, user: user_id } =
    yield* getItem<FriendRequestItemShape>(identity);
  const user = yield* getItem(user_id);

  try {
    const { apiClient, compactUrl } = yield* getGlobalContext();

    yield put({ type: PAGINATION_UN_LIST, payload: identity });
    yield put({ type: 'core/status/decreaseRequest' });

    const config = yield* getItemActionConfig(user, 'acceptFriendRequest');

    yield apiClient.request({
      method: 'put',
      url: compactUrl(config.apiUrl, user),
      data: { action: 'approve' }
    });

    if (requestId) {
      yield* patchEntity(identity, {
        friendship: FRIENDSHIP_IS_FRIEND
      });
    }

    yield* patchEntity(identity, { friendship: FRIENDSHIP_IS_FRIEND });
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* denyFriendRequest({ payload: { identity } }: ItemLocalAction) {
  const { id: requestId, user: userId } =
    yield* getItem<FriendRequestItemShape>(identity);
  const user = yield* getItem(userId);

  try {
    const { apiClient, compactUrl } = yield* getGlobalContext();

    yield put({ type: PAGINATION_UN_LIST, payload: identity });
    yield put({ type: 'core/status/decreaseRequest' });
    const config = yield* getItemActionConfig(user, 'denyFriendRequest');

    yield apiClient.request({
      method: 'put',
      url: compactUrl(config.apiUrl, user),
      params: { action: 'deny' }
    });

    if (requestId) {
      yield* patchEntity(identity, {
        friendship: FRIENDSHIP_CAN_ADD_FRIEND
      });
    }

    yield* patchEntity(identity, {
      friendship: FRIENDSHIP_CAN_ADD_FRIEND
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* cancelRequest({ payload: { identity } }: ItemLocalAction) {
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const item = yield* getItem<FriendRequestItemShape>(identity);

  if (!item) return null;

  const { user: userId } = item;

  const user = yield* getItem(userId);

  const config = yield* getItemActionConfig(user, 'cancelRequest');

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* deleteEntity(identity);

    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, user),
      data: compactData(config.apiParams, user)
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('friend_request/accept', acceptFriendRequest),
  takeEvery('friend_request/deny', denyFriendRequest),
  takeEvery('friend_request/cancel', cancelRequest)
];

export default sagas;
