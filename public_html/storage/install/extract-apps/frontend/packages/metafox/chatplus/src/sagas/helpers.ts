import {
  deleteEntity,
  fulfillEntity,
  getGlobalContext
} from '@metafox/framework';
import { put, select, all, call } from 'redux-saga/effects';
import { MODE_UN_SEARCH, NEW_CHAT_ROOM } from '../constants';
import {
  getChatRoomSelector,
  getEntitiesMessageIdByRoomIdSelector,
  getMessageFilterItemSelector,
  getMessageItemSelector,
  getMessages,
  getRoomItemSelector,
  getSubscriptionItemSelector
} from '../selectors';
import {
  AppState,
  ChatRoomShape,
  MsgItemShape,
  RoomItemShape,
  SubscriptionItemShape
} from '../types';
import { normalizeMsgItem } from '../utils';
import { getFirstRoom } from './chatRoom';
import { isEmpty, cloneDeep } from 'lodash';

export function* putRoomMessages(messagesProp: MsgItemShape[]) {
  if (!messagesProp.length) return;

  const messages = cloneDeep(messagesProp);
  const rid = messages[0].rid;
  const exitsMsg = yield* getEntitiesMessageId(rid);
  const chatRoom = yield select(getChatRoomSelector, rid);
  const isSearching =
    (chatRoom?.msgSearch?.mQuoteId || chatRoom?.msgSearch?.id) &&
    chatRoom?.msgSearch?.mode === MODE_UN_SEARCH;
  messages.forEach(normalizeMsgItem);

  const { normalization } = yield* getGlobalContext();

  const result = yield normalization.normalize(messages);

  yield* fulfillEntity(result.data);

  // filter message before put
  let filterMsg = messages;

  if (exitsMsg) {
    filterMsg = messages.filter(msg => !exitsMsg.includes(msg.id));
  }

  if (isSearching || !isEmpty(chatRoom?.searchMessages)) {
    filterMsg = messages;
  }

  if (!filterMsg?.length) return;

  yield put({
    type: 'chatplus/room/messages',
    payload: { rid, messages: filterMsg }
  });
}

export function* putRoomSearchMessages(
  rid: string,
  mid: string,
  messages: MsgItemShape[]
) {
  if (!messages.length) return;

  messages.forEach(normalizeMsgItem);

  const { normalization } = yield* getGlobalContext();

  const result = yield normalization.normalize(messages);

  yield* fulfillEntity(result.data);

  yield put({
    type: 'chatplus/room/addSearchMessages',
    payload: { rid, mid, messages }
  });
}

export function* openChatRoomFromDock() {
  yield* addRoomToChatDock(NEW_CHAT_ROOM);
}

export function* addRoomToChatDock(rid: string, text?: string) {
  yield put({
    type: 'chatplus/openRooms/addRoomToChatDock',
    payload: { rid, text }
  });
}

export function* removeRoomFromChatDock(rid: string) {
  const { getPageParams, navigate } = yield* getGlobalContext();
  const pageParam: any = getPageParams();

  const redirectRoom = yield* getFirstRoom({ payload: {} } as any);

  try {
    yield put({
      type: 'chatplus/closePanel',
      payload: { identity: rid }
    });

    if (pageParam?.rid === rid && navigate) {
      navigate(redirectRoom ? `/messages/${redirectRoom.id}` : '/messages');
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
          .filter(message => message.rid === rid)
          .map(message => call(deleteEntity, message._identity))
      );
    }
  } catch (error) {
    // err
  }
}

export function* getRoomItem(
  rid: string
): Generator<unknown, RoomItemShape, unknown> {
  return (yield select(getRoomItemSelector, rid)) as any;
}

export function* getSubscriptionItem(
  rid: string
): Generator<unknown, SubscriptionItemShape, unknown> {
  return (yield select(getSubscriptionItemSelector, rid)) as any;
}

export function* getChatRoomItem(
  rid: string
): Generator<unknown, ChatRoomShape, unknown> {
  return (yield select(getChatRoomSelector, rid)) as any;
}

export function* getMessageItem(
  msgId: string
): Generator<unknown, MsgItemShape, unknown> {
  return (yield select(getMessageItemSelector, msgId)) as any;
}

export function* getFilterMessageItem(
  rid: string
): Generator<unknown, MsgItemShape, unknown> {
  return (yield select(getMessageFilterItemSelector, rid)) as any;
}

export function* getEntitiesMessageId(
  rid: string
): Generator<unknown, Array<any>, unknown> {
  return (yield select(getEntitiesMessageIdByRoomIdSelector, rid)) as any;
}
