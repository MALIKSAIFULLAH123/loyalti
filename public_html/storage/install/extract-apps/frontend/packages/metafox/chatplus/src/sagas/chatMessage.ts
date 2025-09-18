/**
 * @type: saga
 * name: chatplus.saga.chatMessage
 */

import {
  deleteEntity,
  fulfillEntity,
  getGlobalContext,
  getItem,
  LocalAction
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import { put, select, takeEvery } from 'redux-saga/effects';
import { getChatUser, getOpenChatRoomsSelector } from '../selectors';
import {
  AppState,
  MsgItemShape,
  RoomItemShape,
  SubscriptionItemShape,
  RoomType
} from '../types';
import { normalizeRoomItem, normalizeSubscriptionItem } from '../utils';
import { putRoomMessages, removeRoomFromChatDock } from './helpers';

function* upsertMessages(action: LocalAction<MsgItemShape[]>) {
  yield* putRoomMessages(action.payload);
}

function* upsertSubscriptions(action: LocalAction<SubscriptionItemShape>) {
  const data = action.payload;
  normalizeSubscriptionItem(data);
  const { normalization } = yield* getGlobalContext();

  // if you keep the same id,
  // it will create a new item, so it won't update the correct item
  const customData = { ...data, id: data.rid };

  const result = yield normalization.normalize(customData);

  const subscriptions = result.data;

  yield* fulfillEntity(subscriptions);

  yield* fulfillEntity({
    chatplus: {
      entities: {
        buddy: {
          [data.rid]: {
            id: data.rid,
            name: data.fname,
            t: data.t,
            avatarETag: data?.other?.avatarETag,
            username:
              data.t === RoomType.Direct
                ? data?.other?.username || data.name
                : `@${data.fname || data.name}`
          }
        }
      }
    }
  });

  if (data) {
    yield put({
      type: 'chatplus/room/add',
      payload: {
        subscription: data
      }
    });
  }
}

function* removeSubscriptions(action: LocalAction<SubscriptionItemShape>) {
  try {
    const { _id, rid } = action.payload;

    const identityRoom = `chatplus.entities.room.${rid}`;
    const identitySubscription = `chatplus.entities.subscription.${_id}`;
    const roomItem = yield* getItem(identityRoom);

    if (!roomItem || !identitySubscription) return;

    yield* deleteEntity(identityRoom);
    yield* deleteEntity(identitySubscription);

    yield* removeRoomFromChatDock(rid);

    yield put({
      type: 'chatplus/room/delete',
      payload: {
        identity: rid
      }
    });
  } catch (error) {
    // handle error
  }
}

function* upsertNewRoom(action: LocalAction<RoomItemShape & { rid: string }>) {
  const data = action.payload;

  const customData = { ...data };

  // hotfix for BE event rooms-change have rid wrong
  // if (data.rid) return;
  customData._id = data.rid || customData._id;

  normalizeRoomItem(customData);
  const { normalization } = yield* getGlobalContext();

  const result = yield normalization.normalize(customData);

  yield* fulfillEntity(result.data);
}

function* upsertRooms(action: LocalAction<RoomItemShape & { rid: string }>) {
  const data = action.payload;

  const customData = { ...data };

  // hotfix for BE event rooms-change have _id wrong
  if (data.rid) return;
  // customData._id = data.rid;

  normalizeRoomItem(customData);
  const { normalization } = yield* getGlobalContext();

  const result = yield normalization.normalize(customData);

  yield* fulfillEntity(result.data);

  if (customData) {
    yield put({
      type: 'chatplus/room/add',
      payload: {
        room: customData
      }
    });
  }
}

function* openRoomNewMessage(
  action: LocalAction<RoomItemShape & { rid: string }>
) {
  const data = action.payload;

  if (!data?.rid) return;

  const openRooms = yield select(getOpenChatRoomsSelector);

  const openRoomItem = openRooms.values.find(item => item.rid === data.rid);

  if (isEmpty(openRooms.values) || !openRoomItem) {
    yield put({
      type: 'chatplus/openRooms/addRoomToChatDock',
      payload: { rid: data.rid }
    });
  }
}

function* removedRooms(
  action: LocalAction<AppState['entities']['room'], { rid: string }>
) {
  const data = action.payload;

  if (data) {
    yield put({
      type: 'chatplus/room/delete',
      payload: {
        identity: data.rid
      }
    });
  }
}

function* typingRooms(
  action: LocalAction<
    AppState['entities']['room'],
    { rid: string; username: string; status: boolean; params?: any }
  >
) {
  const { rid, username, status, params } = action.payload;

  try {
    const entityRoom = `chatplus.entities.room.${rid}`;

    const user = yield select(getChatUser);

    if (user?.username === username) return;

    const itemRoom = yield* getItem(entityRoom);

    let typingList = Object.assign([], itemRoom?.typing || []);

    const avatarETag = params?.typingUser?.avatarETag;
    const name = params?.typingUser?.name;

    const findItem = typingList.find(item => item?.username === username);

    if (status) {
      if (!findItem) {
        typingList.push({ name, username, avatarETag });
      }
    } else {
      if (findItem) {
        typingList = typingList.filter(e => e.username !== username);
      }
    }

    const { normalization } = yield* getGlobalContext();

    const customData = { ...itemRoom, typing: [...typingList] };
    const result = yield normalization.normalize(customData);
    yield* fulfillEntity(result.data);
  } catch (error) {
    // console.log(error);
  }
}

const sagas = [
  takeEvery('chatplus/addRoomMessages', upsertMessages),
  takeEvery('chatplus/subscription/upsert', upsertSubscriptions),
  takeEvery('chatplus/subscription/remove', removeSubscriptions),
  takeEvery('chatplus/rooms/typing', typingRooms),
  takeEvery('chatplus/rooms/upsert', upsertRooms),
  takeEvery('chatplus/rooms/upsertNew', upsertNewRoom),
  takeEvery('chatplus/rooms/removed', removedRooms),
  takeEvery('chatplus/users/openRoomNewMessage', openRoomNewMessage)
];

export default sagas;
