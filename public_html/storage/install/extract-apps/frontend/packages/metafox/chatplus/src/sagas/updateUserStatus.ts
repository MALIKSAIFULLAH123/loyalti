/**
 * @type: saga
 * name: saga.chatplus.updateUserStatus
 */

import {
  getGlobalContext,
  getItem,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import { takeEvery, all, call } from 'redux-saga/effects';
import { ChatVisibilityStatus } from '../types';

const FRIENDSHIP_IS_FRIEND = 1;
const FRIENDSHIP_IS_OWNER = 5;

interface UsersShape {
  [key: string]: any;
}

function* funcStatusUser(user, status) {
  const item = yield* getItem(user?._identity);

  if (!item) return;

  yield* patchEntity(user?._identity, { status_user: parseInt(status) });
}

function* updateUserStatus(
  action: ItemLocalAction & { payload: { users: UsersShape } }
) {
  const { users } = action.payload;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { chatplus, getSetting } = yield* getGlobalContext();

  const chat_visibility: ChatVisibilityStatus = getSetting(
    'chatplus.chat_visibility'
  );

  if (isEmpty(users)) return null;

  let data = Object.keys(users);

  if (chat_visibility === ChatVisibilityStatus.Friendship) {
    data = Object.keys(users).filter(userId => {
      if (!users?.[userId]) return;

      return (
        (users[userId].friendship === FRIENDSHIP_IS_FRIEND ||
          users[userId].friendship === FRIENDSHIP_IS_OWNER) &&
        isEmpty(users[userId]?.status_user)
      );
    });
  }

  if (isEmpty(data)) return null;

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'getPresence',
      params: [data]
    });

    if (isEmpty(result) && !result?.length) return;

    yield all(
      result.map(item =>
        call(funcStatusUser, users[item?.metafoxUserId], item?.status)
      )
    );
  } catch (error) {
    // err
  }
}

const sagas = [takeEvery('chatplus/updateUserStatus', updateUserStatus)];

export default sagas;
