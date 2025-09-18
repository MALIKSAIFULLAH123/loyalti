/**
 * @type: saga
 * name: chatplus.saga.message
 */

import {
  AppResourceAction,
  getGlobalContext,
  getItem,
  handleActionConfirm,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { isFunction, omit } from 'lodash';
import { takeEvery, takeLatest, put } from 'redux-saga/effects';
import { MsgItemShape } from '../types';
import handleActionErrorChat from './handleActionErrorChat';
import { getMessageItem, getRoomItem, putRoomSearchMessages } from './helpers';
import { JUMP_MSG_ACTION } from '../constants';
import { TypeActionPinStar } from '../components/ChatRoomPanel/Header/Header';
import { formatTextCopy } from '../services/formatTextMsg';

function* getDeleteMessage(action: ItemLocalAction) {
  const message = action.payload;
  const identity = `chatplus.entities.message.${message[0]._id}`;

  try {
    const item = yield* getMessageItem(identity);

    if (!item) return;

    yield* patchEntity(identity, {
      deleted: true,
      msgType: 'deleted',
      msgContentType: 'messageDeleted'
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* deleteMessageItem({ payload: { identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);
    const { chatplus, i18n } = yield* getGlobalContext();

    if (!item) return;

    const config = {
      confirm: {
        title: i18n.formatMessage({ id: 'are_you_sure' }),
        message: i18n.formatMessage({ id: 'delete_msg' })
      }
    };

    const ok = yield handleActionConfirm(config as AppResourceAction);

    if (!ok) return;

    yield* patchEntity(identity, { deleted: true });

    const result = yield chatplus
      .waitDdpMethod({
        name: 'deleteMessage',
        params: [item]
      })
      .then(() => {
        return true;
      })
      .catch(err => {
        return false;
      });

    if (!result) return;

    yield put({
      type: 'chatplus/room/deleteItemMessagesFilter',
      payload: {
        id_message: item?._id,
        rid: item?.rid
      }
    });
  } catch (error) {
    yield* patchEntity(identity, { deleted: false });
    yield* handleActionErrorChat(error);
  }
}

function* starMessageItem({ payload: { identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);
    const { chatplus, toastBackend, i18n } = yield* getGlobalContext();

    if (!item) return;

    const result = yield chatplus.waitDdpMethod({
      name: 'chatplus/starMessage',
      params: [item._id]
    });

    if (!result) {
      // handle error
    }

    toastBackend.success(
      i18n.formatMessage({ id: 'star_messages_successfully' })
    );
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unStarMessageItem({ payload: { identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);
    const { chatplus, toastBackend, i18n } = yield* getGlobalContext();

    if (!item) return;

    return chatplus
      .waitDdpMethod({
        name: 'chatplus/starMessage',
        params: [item._id]
      })
      .then(() => {
        toastBackend.success(
          i18n.formatMessage({ id: 'remove_star_messages_successfully' })
        );

        return true;
      })
      .catch(err => {
        return false;
      });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* copyMessageItem({ payload: { identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);
    const { copyToClipboard, toastBackend, i18n } = yield* getGlobalContext();

    const message = yield formatTextCopy(item);

    if (!message || !item) return;

    yield copyToClipboard(message);

    toastBackend.success(i18n.formatMessage({ id: 'copied_to_clipboard' }));
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* reactMessage({ payload: { shortcut, identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);

    if (!item) return;

    const { chatplus } = yield* getGlobalContext();

    yield chatplus.waitDdpMethod({
      name: 'setReaction',
      params: [shortcut, item._id, true]
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unsetReactMessage(
  action: ItemLocalAction<
    { identity: string; shortcut: string },
    { onSuccess?: () => void }
  >
) {
  try {
    const { identity, shortcut } = action.payload;
    const item = yield* getItem<MsgItemShape>(identity);

    if (!item) return;

    const { chatplus } = yield* getGlobalContext();

    yield chatplus.waitDdpMethod({
      name: 'setReaction',
      params: [shortcut, item._id, false]
    });
    isFunction(action?.meta.onSuccess) && action.meta.onSuccess();
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* replyMessageItem(action: ItemLocalAction) {
  yield;
}

function* quoteMessageItem(action: ItemLocalAction) {
  yield;
}

function* editMessageItem(action: ItemLocalAction) {
  yield;
}

function* pinMessageItem({ payload: { identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);

    if (!item) return;

    const { chatplus } = yield* getGlobalContext();

    yield chatplus.waitDdpMethod({
      name: 'pinMessage',
      params: [item]
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unpinMessageItem({ payload: { identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);

    if (!item) return;

    const { chatplus } = yield* getGlobalContext();

    return chatplus
      .waitDdpMethod({
        name: 'unpinMessage',
        params: [item]
      })
      .then(() => {
        return true;
      })
      .catch(err => {
        return false;
      });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unpinAndremoveMessageItem({
  payload: { identity }
}: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);

    if (!item) return;

    const result = yield* unpinMessageItem({ payload: { identity } } as any);

    if (!result) return;

    yield put({
      type: 'chatplus/room/deleteItemMessagesFilter',
      payload: {
        id_message: item._id,
        rid: item.rid
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* unStarAndremoveMessageItem({
  payload: { identity }
}: ItemLocalAction) {
  try {
    const item = yield* getItem<MsgItemShape>(identity);

    if (!item) return;

    const result = yield* unStarMessageItem({ payload: { identity } } as any);

    if (!result) return;

    yield put({
      type: 'chatplus/room/deleteItemMessagesFilter',
      payload: {
        id_message: item._id,
        rid: item.rid
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* presentReactionsList(action: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();

  try {
    const { identity } = action.payload;
    const item = yield* getItem<MsgItemShape>(identity);

    if (!item) return;

    yield dialogBackend.present({
      component: 'dialog.chatplus.PresentReactionsList',
      props: { identity }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* jumpMessage(action: {
  type: string;
  payload: {
    roomId: string;
    mid: string;
    operate?: 'gt' | 'lt' | 'all';
    limit?: number;
    identity?: string;
    type?: TypeActionPinStar;
    mode?: 'quote' | string;
  };
  meta?: { onSuccess?: () => void; onFailure?: () => void };
}) {
  const { onSuccess } = action?.meta || {};

  const {
    roomId,
    mid,
    operate = 'all',
    limit = 20,
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    identity: identityPayload = null,
    type,
    mode
  } = action.payload;
  const room = yield* getRoomItem(roomId);

  yield put({
    type: 'chatplus/room/deleteMessagesFilter',
    payload: {
      identity: roomId
    }
  });

  const { chatplus } = yield* getGlobalContext();

  if (!room || !mid) return null;

  try {
    yield put({
      type: 'chatplus/room/searchLoading',
      payload: {
        rid: room.id,
        loading: true
      }
    });

    // server params => __searchRoomMessages(rid, searchValue, limit, beforeDate, extra = {})  // { mid, operate, lmt} = extra;
    const resultChatplus = yield chatplus.waitDdpMethod({
      name: '__searchRoomMessages',
      params: [room.id, '', limit, null, { mid, operate, lmt: limit }]
    });

    if (!resultChatplus) return;

    const { messages: msgResult } = resultChatplus;
    const { gt = [], lt = [] } = msgResult[0]?.related || {};

    const messages = [omit(msgResult[0], ['related']), ...gt, ...lt];

    if (!messages.length) return;

    yield* putRoomSearchMessages(room.id, mid, messages);

    yield put({
      type: 'chatplus/room/setMessageSearch',
      payload: {
        rid: room.id,
        mid,
        slot: 0,
        mode,
        ...(mode !== 'quote' && {
          msgIds: [mid],
          total: 1
        })
      }
    });

    if (type === 'pin') {
      yield put({
        type: 'chatplus/room/pinnedMessages',
        payload: { identity: room.id }
      });
    }

    if (type === 'star') {
      yield put({
        type: 'chatplus/room/starredMessages',
        payload: { identity: room.id }
      });
    }

    typeof onSuccess === 'function' && onSuccess();

    yield put({
      type: 'chatplus/room/searchLoading',
      payload: {
        rid: room.id,
        loading: false
      }
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

const sagas = [
  takeEvery('chatplus/getDeleteMessage', getDeleteMessage),
  takeLatest('chatplus/deleteMessage', deleteMessageItem),
  takeLatest('chatplus/starMessage', starMessageItem),
  takeLatest('chatplus/copyMessage', copyMessageItem),
  takeLatest('chatplus/replyMessage', replyMessageItem),
  takeLatest('chatplus/quoteMessage', quoteMessageItem),
  takeLatest('chatplus/editMessage', editMessageItem),
  takeLatest('chatplus/pinMessage', pinMessageItem),
  takeLatest('chatplus/unpinMessage', unpinMessageItem),
  takeLatest('chatplus/unstarMessage', unStarMessageItem),
  takeLatest('chatplus/messageReaction', reactMessage),
  takeLatest('chatplus/unsetReaction', unsetReactMessage),
  takeLatest('chatplus/presentReactionsList', presentReactionsList),
  takeLatest('chatplus/unpinAndremoveMessage', unpinAndremoveMessageItem),
  takeLatest('chatplus/unstarAndremoveMessage', unStarAndremoveMessageItem),
  takeLatest(JUMP_MSG_ACTION, jumpMessage)
];

export default sagas;
