import {
  deleteEntity,
  fulfillEntity,
  getGlobalContext
} from '@metafox/framework';
import { put, select, all, call } from 'redux-saga/effects';
import {
  getChatRoomSelector,
  getEntitiesMessageIdByRoomIdSelector,
  getMessageItemSelector,
  getMessages,
  getRoomItemSelector
} from '../selectors';
import { MsgItemShape, RoomItemShape, AppState, ChatRoomShape } from '../types';
import { normalizeMsgItem } from '../utils';
import { checkRemoveOpenRoom } from './chatRoom';

export function* getEntitiesMessageId(
  rid: string
): Generator<unknown, Array<any>, unknown> {
  return (yield select(getEntitiesMessageIdByRoomIdSelector, rid)) as any;
}

export function* getMessageItem(
  msgId: string
): Generator<unknown, MsgItemShape, unknown> {
  return (yield select(getMessageItemSelector, msgId)) as any;
}

export function* getChatRoomItem(
  rid: string
): Generator<unknown, ChatRoomShape, unknown> {
  return (yield select(getChatRoomSelector, rid)) as any;
}

export function* getRoomItem(
  rid: string
): Generator<unknown, RoomItemShape, unknown> {
  return (yield select(getRoomItemSelector, rid)) as any;
}

export function* putRoomMessages(messages: MsgItemShape[]) {
  if (!messages.length) return;

  const rid = messages[0].room_id;
  const exitsMsg = yield* getEntitiesMessageId(messages[0].room_id);
  const { normalization } = yield* getGlobalContext();

  messages.forEach(normalizeMsgItem);
  const result = yield normalization.normalize(messages);
  yield* fulfillEntity(result.data);

  // filter message before put
  const filterMsg = exitsMsg
    ? messages.filter(msg => {
        if (!exitsMsg.includes(msg.id.toString())) {
          return msg;
        }
      })
    : messages;

  if (!filterMsg?.length) return;

  yield put({
    type: 'chat/chatroom/messages',
    payload: { rid, messages: filterMsg }
  });
}

export function* putRoomSearchMessages(
  rid: string,
  mid: string,
  messages: MsgItemShape[]
) {
  if (!messages.length) return;

  const { normalization } = yield* getGlobalContext();

  messages.forEach(normalizeMsgItem);
  const result = yield normalization.normalize(messages);
  yield* fulfillEntity(result.data);

  yield put({
    type: 'chat/room/addSearchMessages',
    payload: { rid, mid, messages }
  });
}

export function* removeRoomDock(rid: string) {
  const { getPageParams, navigate } = yield* getGlobalContext();
  const pageParam: any = getPageParams();

  try {
    const identityRoom = `chat.entities.room.${rid}`;

    yield* deleteEntity(identityRoom);

    yield* checkRemoveOpenRoom({ payload: { rid } } as any);

    yield put({
      type: 'chat/room/delete',
      payload: {
        identity: rid
      }
    });

    yield put({
      type: 'chat/closePanel',
      payload: { identity: rid }
    });

    // eslint-disable-next-line eqeqeq
    if (pageParam?.rid == rid && navigate) {
      navigate('/messages');
    }
  } catch (error) {
    // err
  }
}

export function* removeMessagesRoom(rid: string) {
  const messages: AppState['entities']['message'] = yield select(getMessages);

  try {
    if (messages && Object.values(messages).length) {
      yield all(
        Object.values(messages)
          .filter(message => message.room_id === rid)
          .map(message => call(deleteEntity, message._identity))
      );
    }
  } catch (error) {
    // err
  }
}
