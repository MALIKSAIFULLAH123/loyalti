/**
 * @type: saga
 * name: chat.saga.chatRoom
 */

import {
  FormSubmitAction,
  fulfillEntity,
  getGlobalContext,
  getItem,
  getResourceConfig,
  getSession,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  LocalAction,
  PAGINATION_REFRESH,
  patchEntity
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import {
  put,
  takeEvery,
  takeLatest,
  all,
  call,
  select
} from 'redux-saga/effects';
import { CHAT_ROOMS, NEW_CHAT_ROOM } from '../constants';
import { getFirstRoomSelector, getRooms } from '../selectors';
import { normalizeMsgItem } from '../utils';
import { AppState } from '../types';
import { getRoomItem } from './helpers';
import { updateRoom } from './room';

function* reloadRoomList() {
  yield put({
    type: PAGINATION_REFRESH,
    payload: {
      apiUrl: '/chat-room',
      apiParams: {},
      pagingId: '/chat-room?'
    }
  });
}

export function* checkRemoveOpenRoom(action: LocalAction<{ rid: string }>) {
  const { localStore } = yield* getGlobalContext();

  const { user } = yield* getSession();

  if (!user) return;

  try {
    const { rid } = action.payload;
    const { id } = user;
    const data = localStore.get(CHAT_ROOMS);

    let result = data ? JSON.parse(data)[id] : [];

    if (!isEmpty(result)) {
      result = result.filter(item => item.rid !== rid);
    }

    result = { [id]: result };

    localStore.set(CHAT_ROOMS, JSON.stringify(result));
  } catch (err) {
    // err
  }
}

export function* openChatRoom(action: {
  type: string;
  payload: { identity: string; isMobile?: boolean };
  meta: {
    onSuccess?: () => void;
    onError?: () => void;
    onFinally?: () => void;
  };
}) {
  const { onSuccess, onError, onFinally } = action?.meta || {};

  try {
    const { identity, isMobile, text } = action.payload;
    const user = yield* getItem(identity);

    if (user) {
      yield put({
        type: 'chat/newChatRoom',
        payload: { user, isPage: false, isMobile, text }
      });
    }

    onSuccess && onSuccess();
  } catch (error) {
    onError && onError();
    yield* handleActionError(error);
  } finally {
    onFinally && onFinally();
  }
}

function* newChatRoom(
  action: LocalAction<{ user: any; isPage: boolean; isMobile?: boolean }>
) {
  const { navigate, apiClient, normalization } = yield* getGlobalContext();
  const { user, isPage, text } = action.payload;

  try {
    const response = yield apiClient.request({
      method: 'POST',
      url: '/chat-room',
      data: { member_ids: [user.id] }
    });

    const data = response?.data?.data;

    if (!data && !data.id) return;

    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);

    if (isPage || action.payload?.isMobile) {
      yield put({
        type: 'chat/room/text',
        payload: {
          rid: data.id,
          text
        }
      });

      navigate(`/messages/${data.id}`);
    } else {
      yield put({
        type: 'chat/openRoomPanel',
        payload: { rid: data.id, text }
      });

      yield put({
        type: 'chat/closePanel',
        payload: { identity: NEW_CHAT_ROOM }
      });
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* addConversation({ payload: {} }: ItemLocalAction) {
  try {
    const { dialogBackend } = yield* getGlobalContext();

    const dataSource = yield* getResourceConfig('chat', 'room', 'addForm');

    yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'xs'
      }
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* submitAddConversation({
  payload: { values, method, form, action }
}: FormSubmitAction) {
  const { apiClient, dialogBackend, navigate } = yield* getGlobalContext();

  if (!values?.users?.id) return;

  const member_ids = [values.users.id];

  try {
    const response = yield apiClient.request({
      method,
      url: action,
      data: { member_ids }
    });

    yield* handleActionFeedback(response);

    const result = response.data.data;

    if (result) {
      yield* reloadRoomList();

      if (result?.id) {
        navigate(`/messages/${result.id}`);
      }
    }

    dialogBackend.dismiss();
  } catch (error) {
    yield* handleActionError(error, form);
  } finally {
    form.setSubmitting(false);
  }
}

function* openInMessenger(action: ItemLocalAction) {
  const { identity } = action.payload;
  const room = yield* getRoomItem(identity);

  if (!room) return;

  const { navigate } = yield* getGlobalContext();

  try {
    navigate(`/messages/${room?.id}`);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* toggleChatRoom(action: ItemLocalAction) {
  const { identity: rid } = action.payload;

  try {
    yield put({ type: 'chat/openRooms/toggleCollapsedPanel', payload: rid });
  } catch (error) {
    // err;
  }
}

export function* presentImageView(
  action: LocalAction<{ image: any; images: any[] }>
) {
  const { dialogBackend } = yield* getGlobalContext();
  const { image = {}, images = [] } = action.payload;

  if (isEmpty(image)) return;

  return yield dialogBackend.present({
    component: 'chat.dialog.ImageView',
    props: {
      image,
      images: images.length ? images : [image]
    }
  });
}

export function* saveChatRoomsLocal(action: LocalAction) {
  const { localStore } = yield* getGlobalContext();

  const { user } = yield* getSession();

  if (!user) return;

  try {
    const data = action.payload;
    const { id } = user;
    const result = { [id]: data };
    localStore.set(CHAT_ROOMS, JSON.stringify(result));

    return data;
  } catch (err) {
    // err
  }
}

function* getFulfillRoom(rid: string) {
  const { apiClient, normalization } = yield* getGlobalContext();

  try {
    const responseRoom = yield apiClient.request({
      url: `/chat-room/${rid}`
    });

    const dataRoom = responseRoom?.data?.data;
    const resultRoom = normalization.normalize(dataRoom);

    yield* fulfillEntity(resultRoom.data);
  } catch (error) {
    yield* checkRemoveOpenRoom({ payload: { rid } } as any);
    yield* handleActionError(error);
  }
}

function* saveDataRoomStore(action: LocalAction<{ openRooms: any[] }>) {
  const { openRooms } = action.payload;

  try {
    return yield all(openRooms.map(room => call(getFulfillRoom, room.rid)));
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* getRoomsLocal(action: LocalAction) {
  const { localStore } = yield* getGlobalContext();

  const { user } = yield* getSession();

  if (!user) return;

  try {
    const { id } = user;
    const data = localStore.get(CHAT_ROOMS);

    const result = data ? JSON.parse(data)[id] : [];

    if (result.length) {
      yield put({
        type: 'chat/openRooms/init',
        payload: { openRooms: result }
      });
      yield all(result.map(room => call(getFulfillRoom, room.rid)));
    }
  } catch (err) {
    // err
  }
}

export function* getFirstRoom(
  action: ItemLocalAction<{ rid?: string }, { onSuccess?: (values) => void }>
) {
  try {
    const room = yield select(getFirstRoomSelector);

    if (!room) return null;

    action?.meta?.onSuccess && action?.meta?.onSuccess(room);

    return room;
  } catch (error) {
    // error
  }
}

function* navigateMessagesPage() {
  const { navigate } = yield* getGlobalContext();

  try {
    const roomFirst = yield* getFirstRoom({ payload: {} } as any);

    if (isEmpty(roomFirst)) navigate('/messages');

    navigate(`/messages/${roomFirst.id}`, { replace: true });
  } catch (error) {
    // error
  }
}

function* markAsRead(action: ItemLocalAction) {
  const { identity: id } = action.payload;

  const identity = `chat.entities.room.${id}`;

  const room = yield* getItem(identity);

  if (!room || !room?.total_unseen) return;

  const { apiClient } = yield* getGlobalContext();

  try {
    const response = yield apiClient.request({
      method: 'PUT',
      url: `/chat-room/mark-read/${room.id}`
    });

    const result = response.data.data;

    if (result) {
      yield* patchEntity(identity, {
        total_unseen: 0
      });
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* searchMessage(action: {
  type: string;
  payload: {
    roomId: string;
    text: string;
    filter?: string;
  };
  meta?: { onSuccess: (value) => void; onFailure: () => void };
}) {
  const { roomId: rid, text } = action.payload;
  const room = yield* getRoomItem(rid);
  const { dispatch, apiClient } = yield* getGlobalContext();

  if (!rid || !room) return;

  if (!text) {
    dispatch({
      type: 'chat/room/deleteMessagesFilter',
      payload: {
        identity: rid
      }
    });

    return;
  }

  try {
    const responseFilter = yield apiClient.request({
      url: '/chat',
      params: { room_id: rid, limit: 100, q: text || undefined }
    });

    const messages = responseFilter?.data?.data;

    if (!messages) return;

    action?.meta?.onSuccess && action?.meta?.onSuccess(messages);

    dispatch({
      type: 'chat/room/deleteMessagesFilter',
      payload: {
        identity: rid
      }
    });

    // messages.forEach(normalizeMsgItem);

    const dataObj = {};

    messages.forEach(item => {
      normalizeMsgItem(item);

      dataObj[item.id] = Object.assign(item, {
        filtered: true
      });
    });

    dispatch({
      type: 'chat/room/saveMessagesFilter',
      payload: {
        identity: rid,
        data: dataObj
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* markAllRead(action: ItemLocalAction) {
  const { apiClient } = yield* getGlobalContext();

  try {
    const response = yield apiClient.request({
      method: 'PUT',
      url: '/chat-room/mark-all-read'
    });

    const status = response?.data?.status;
    const rooms: AppState['entities']['room'] = yield select(getRooms);

    if (status === 'success' && rooms && Object.keys(rooms).length) {
      yield all(
        Object.values(rooms)
          .filter(room => room.total_unseen && room.total_unseen > 0)
          .map(room =>
            call(updateRoom, {
              payload: { id: room.id, total_unseen: 0 }
            } as any)
          )
      );
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* getChatRoomList(action: { type: string }) {
  const { apiClient, normalization } = yield* getGlobalContext();

  try {
    const response = yield apiClient.request({
      url: '/chat-room'
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('chat/room/openChatRoom', openChatRoom),
  takeEvery('chat/newChatRoom', newChatRoom),
  takeEvery('chat/chatroom/addConversation', addConversation),
  takeEvery('@chat/addConversation', submitAddConversation),
  takeEvery('chat/room/openInMessenger', openInMessenger),
  takeLatest('chat/room/toggle', toggleChatRoom),
  takeLatest('chat/room/presentImageView', presentImageView),
  takeLatest('chat/saveChatRoomsLocal', saveChatRoomsLocal),
  takeLatest('chat/getRoomsLocal', getRoomsLocal),
  takeLatest('chat/saveDataRoomStore', saveDataRoomStore),
  takeLatest('chat/getFirstRoom', getFirstRoom),
  takeLatest('chat/navigateMessagesPage', navigateMessagesPage),
  takeEvery('chat/room/markAsRead', markAsRead),
  takeLatest('chat/room/searchMessages', searchMessage),
  takeEvery('chat/room/markAllRead', markAllRead),
  takeEvery('chat/room/getChatRoomList', getChatRoomList)
];

export default sagas;
