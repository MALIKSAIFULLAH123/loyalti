/**
 * @type: saga
 * name: saga.chatplus.searchMessageRoom
 */
import { fulfillEntity, getGlobalContext } from '@metafox/framework';
import {
  all,
  call,
  put,
  takeEvery,
  takeLatest,
  select
} from 'redux-saga/effects';
import { normalizeMsgItem } from '../utils';
import handleActionErrorChat from './handleActionErrorChat';
import { getMessageItem, getRoomItem, putRoomSearchMessages } from './helpers';
import { isEmpty, omit } from 'lodash';
import { getChatRoomSelector } from '../selectors';
import { LIMIT_SEARCH_MESSAGE } from '../constants';

function* handleSearchMessage(roomId: string, mid: string, msg?: any) {
  const { normalization } = yield* getGlobalContext();

  const room = yield* getRoomItem(roomId);

  const identity = `chatplus.entities.message.${mid}`;

  let message = yield* getMessageItem(identity);

  if (!message) {
    yield normalizeMsgItem(omit(msg, ['related']) as any);

    message = msg;

    const result = yield normalization.normalize(message);
    yield* fulfillEntity(result.data);
  }

  if (!room || !mid) return null;

  try {
    const { gt = [], lt = [] } = msg?.related || {};

    const messages = [omit(message, ['related']), ...gt, ...lt];

    if (!messages.length) return;

    yield* putRoomSearchMessages(roomId, mid, messages);
  } catch (error) {
    // yield* handleActionErrorChat(error);
  }
}

function* pinnedMessages(action: {
  type: string;
  payload: {
    roomId: string;
    text?: string;
    filter: string;
    limit?: number;
    beforeDate?: number;
    isBuddy?: boolean;
  };
  meta?: { onSuccess: (value) => void; onFailure: () => void };
}) {
  const {
    roomId,
    text = '',
    filter,
    limit = LIMIT_SEARCH_MESSAGE,
    beforeDate = null,
    isBuddy = false
  } = action.payload;
  const room = yield* getRoomItem(roomId);
  const { chatplus, dispatch } = yield* getGlobalContext();
  const identity = roomId;

  if (!text && isBuddy) {
    yield put({
      type: 'chatplus/room/clearMsgSearch',
      payload: {
        rid: room.id
      }
    });
  }

  if (!text && !filter) {
    dispatch({
      type: 'chatplus/room/deleteMessagesFilter',
      payload: {
        identity
      }
    });

    return;
  }

  try {
    const customText = filter ? `has:${filter}${text}` : '';

    if (!customText) return;

    const result = yield chatplus.waitDdpMethod({
      name: '__searchRoomMessages',
      params: [room.id, customText, limit, beforeDate]
    });

    if (!result) return;

    action?.meta?.onSuccess && action?.meta?.onSuccess(result);

    dispatch({
      type: 'chatplus/room/deleteMessagesFilter',
      payload: {
        identity
      }
    });

    const { messages } = result;

    const dataObj = {};

    messages.forEach(item => {
      normalizeMsgItem(item);

      dataObj[item._id] = Object.assign(item, {
        filtered: true
      });
    });

    dispatch({
      type: 'chatplus/room/saveMessagesFilter',
      payload: {
        identity,
        data: dataObj
      }
    });

    if (!messages.length && isBuddy) {
      yield put({
        type: 'chatplus/room/clearMsgSearch',
        payload: {
          rid: room.id
        }
      });

      return;
    }
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* searchMessage(action: {
  type: string;
  payload: {
    roomId: string;
    text?: string;
    filter: string;
    limit?: number;
    beforeDate?: number;
  };
  meta?: { onSuccess: (value) => void; onFailure: () => void };
}) {
  const {
    roomId,
    text = '',
    filter,
    limit = LIMIT_SEARCH_MESSAGE,
    beforeDate = null
  } = action.payload;
  const room = yield* getRoomItem(roomId);
  const { chatplus } = yield* getGlobalContext();
  const chatRoom = yield select(getChatRoomSelector, roomId);

  if (isEmpty(room)) return;

  if (chatRoom?.msgSearch) {
    yield put({
      type: 'chatplus/room/clearMQuoteSearch',
      payload: {
        rid: roomId
      }
    });
  }

  if (!text && !filter) {
    yield put({
      type: 'chatplus/room/clearMsgSearch',
      payload: {
        rid: room.id
      }
    });

    return;
  }

  try {
    const customText = filter ? `has:${filter}${text}` : text;

    const result = yield chatplus.waitDdpMethod({
      name: '__searchRoomMessages',
      params: [room.id, customText, limit, beforeDate]
    });

    if (!result) return;

    action?.meta?.onSuccess && action?.meta?.onSuccess(result);

    const { messages, total } = result;

    if (!messages.length) {
      yield put({
        type: 'chatplus/room/clearMsgSearch',
        payload: {
          rid: room.id
        }
      });

      return;
    }

    const msgIds = messages.map(item => item._id).reverse();

    yield put({
      type: 'chatplus/room/setMessageSearch',
      payload: {
        rid: room.id,
        mid: messages[0]?._id,
        slot: msgIds.findIndex(item => item === messages[0]?._id),
        total: total || messages.length,
        msgIds
      }
    });

    yield all(
      messages.map(item =>
        call(handleSearchMessage, room.id, item?._id || item?.id, item)
      )
    );
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

const sagas = [
  takeEvery('chatplus/room/pinnedMessages', pinnedMessages),
  takeLatest('chatplus/room/searchMessages', searchMessage)
];

export default sagas;
