/**
 * @type: saga
 * name: saga.chatplus.roomItem
 */

import {
  AppResourceAction,
  deleteEntity,
  getGlobalContext,
  handleActionConfirm,
  ItemLocalAction,
  LocalAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import { RoomType } from '../types';
import handleActionErrorChat from './handleActionErrorChat';
import {
  getChatRoomItem,
  getMessageItem,
  getRoomItem,
  putRoomMessages,
  putRoomSearchMessages,
  removeMessagesRoom,
  removeRoomFromChatDock
} from './helpers';
import { LIMIT_MESSAGE_INIT_ROOM, LIMIT_SEARCH_MESSAGE } from '../constants';
import { omit } from 'lodash';

function* handleRoomLoadHistory({
  payload: { rid, oldest },
  meta
}: LocalAction<
  { rid: string; oldest: NumberConstructor },
  { onSuccess?: () => void; onFailure?: () => void }
>) {
  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.loadRoomHistory(rid, oldest, 20);

    if (!result.messages.length) {
      yield put({
        type: 'chatplus/room/endLoadmoreMessage',
        payload: { rid }
      });

      typeof meta?.onSuccess === 'function' && meta?.onSuccess();

      return;
    }

    yield* putRoomMessages(result.messages);
    typeof meta?.onSuccess === 'function' && meta?.onSuccess();
  } catch (error) {
    typeof meta?.onFailure === 'function' && meta?.onFailure();
    yield* handleActionErrorChat(error);
  }
}

function* handleRoomSearchLoadHistory(action: {
  type: string;
  payload: {
    roomId: string;
    mid: string;
    operate?: 'gt' | 'lt' | 'all';
    limit?: number;
    identity?: string;
  };
  meta?: { onSuccess?: () => void; onFailure?: () => void };
}) {
  const { onSuccess } = action?.meta || {};

  const {
    roomId,
    mid: midPayload,
    operate = 'all',
    limit = LIMIT_SEARCH_MESSAGE,
    identity: identityPayload = null
  } = action.payload;

  const room = yield* getRoomItem(roomId);

  const identity = identityPayload || `chatplus.entities.message.${midPayload}`;

  const message = yield* getMessageItem(identity);

  const chatRoom = yield* getChatRoomItem(roomId);

  const { chatplus } = yield* getGlobalContext();

  if (!room || !midPayload) return null;

  try {
    const mid = chatRoom?.msgSearch?.mQuoteId || chatRoom?.msgSearch?.id;

    // __searchRoomMessages(rid, searchValue, limit, beforeDate, extra = {})  // { mid, operate, lmt} = extra;
    const resultChatplus = yield chatplus.waitDdpMethod({
      name: '__searchRoomMessages',
      params: [
        room.id,
        '',
        limit,
        null,
        { mid: midPayload, operate, lmt: limit }
      ]
    });

    const { messages: msgResult } = resultChatplus || {};
    const { gt = [], lt = [] } = msgResult?.[0]?.related || {};

    if (!lt?.length && operate === 'lt') {
      yield put({
        type: 'chatplus/room/search/endTopLoadmoreMessage',
        payload: { rid: room.id, mid }
      });

      typeof onSuccess === 'function' && onSuccess();

      return;
    }

    if (!gt?.length && operate === 'gt') {
      yield put({
        type: 'chatplus/room/search/endLoadmoreMessage',
        payload: { rid: room.id, mid }
      });

      typeof onSuccess === 'function' && onSuccess();

      return;
    }

    const messages = [omit(message, ['related']), ...gt, ...lt];

    if (!messages.length) return;

    yield* putRoomSearchMessages(room.id, mid, messages);

    typeof onSuccess === 'function' && onSuccess();
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* handleRoomActive({
  payload: { rid },
  meta
}: LocalAction<
  { rid: string },
  { onSuccess?: () => void; onFailure?: () => void }
>) {
  const { chatplus } = yield* getGlobalContext();

  // todo check latest data.
  try {
    yield chatplus.listenStreamNotifyRoom(rid);
    const result = yield chatplus.loadRoomHistory(
      rid,
      null,
      LIMIT_MESSAGE_INIT_ROOM
    );

    yield* putRoomMessages(result.messages);

    if (result.messages.length < LIMIT_MESSAGE_INIT_ROOM) {
      yield put({
        type: 'chatplus/room/endLoadmoreMessage',
        payload: { rid }
      });
    }

    typeof meta?.onSuccess === 'function' && meta?.onSuccess();
  } catch (error) {
    typeof meta?.onFailure === 'function' && meta?.onFailure();
    yield* handleActionErrorChat(error);
  }
}

function* handleRoomInactive(
  action: ItemLocalAction<
    { rid: string },
    { onSuccess?: () => void; onFailure?: () => void }
  >
) {
  const { chatplus } = yield* getGlobalContext();

  const { rid } = action.payload || {};

  const meta = action.meta || {};

  if (!rid) return;

  try {
    yield chatplus.unListenStreamNotifyRoom(rid);

    typeof meta?.onSuccess === 'function' && meta?.onSuccess();

    yield put({
      type: 'chatplus/room/closeSearching',
      payload: { identity: rid }
    });
    yield put({
      type: 'chatplus/room/clearMsgSearch',
      payload: { rid }
    });
    yield put({
      type: 'chatplus/room/resetFilterPinnedStarred',
      payload: {
        identity: rid
      }
    });
  } catch (error) {
    typeof meta?.onFailure === 'function' && meta?.onFailure();
  }
}

function* deleteRoomItem(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { chatplus, i18n } = yield* getGlobalContext();

  const config = {
    confirm: {
      message: i18n.formatMessage({ id: 'delete_room_description' })
    }
  };

  const ok = yield* handleActionConfirm(config as AppResourceAction);

  if (!ok) return;

  try {
    if (room.t === RoomType.Direct) {
      const result = yield chatplus.waitDdpMethod({
        name: 'eraseDirectRoom',
        params: [identity]
      });

      if (!result) return;
    } else {
      const result = yield chatplus.waitDdpMethod({
        name: 'eraseRoom',
        params: [identity]
      });

      if (!result) return;
    }

    const identityRoom = `chatplus.entities.room.${identity}`;

    yield* deleteEntity(identityRoom);

    yield* removeRoomFromChatDock(identity);

    yield* removeMessagesRoom(identity);

    yield chatplus.unListenStreamNotifyRoom(room?._id);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* changeModeSearch(action: ItemLocalAction) {
  const payload = action.payload;

  try {
    yield put({
      type: 'chatplus/room/changeModeSearch',
      payload
    });

    action?.meta?.onSuccess && action.meta.onSuccess();
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

const sagas = [
  takeEvery('chatplus/room/loadHistory', handleRoomLoadHistory),
  takeEvery('chatplus/room/search/loadHistory', handleRoomSearchLoadHistory),
  takeEvery('chatplus/room/active', handleRoomActive),
  takeEvery('chatplus/room/inactive', handleRoomInactive),
  takeEvery('chatplus/room/deleteRoom', deleteRoomItem),
  takeEvery('chatplus/room/modeSearch', changeModeSearch)
];

export default sagas;
