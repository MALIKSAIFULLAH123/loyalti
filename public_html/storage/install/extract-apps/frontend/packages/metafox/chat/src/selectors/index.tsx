import { GlobalState } from '@metafox/framework';
import { get, isEmpty } from 'lodash';
import { createSelector } from 'reselect';
import { AppState, RoomItemShape } from '../types';

type MessageType = AppState['entities']['message'];

export const getActionMenuMessages = (state: GlobalState) =>
  get(state, 'chat.resourceConfig.message.menus.itemActionMenu.items');

export const getEntitiesMessageIdByRoomId = (
  state: GlobalState,
  rid: string
) => {
  if (!state.chat.entities.message) return;

  return Object.keys(state.chat.entities.message).filter(
    key => state.chat.entities.message[key].room_id === rid
  );
};

export const getEntitiesMessageIdByRoomIdSelector = createSelector(
  getEntitiesMessageIdByRoomId,
  data => data
);

export const getChatRoomItem = (state: GlobalState, rid: string) =>
  get(state, `chat.chatRooms.${rid}`, {});

export const getChatRoomSelector = createSelector(
  getChatRoomItem,
  room => room
);

export const getChatRoomMsgDelete = (state: GlobalState, rid: string) =>
  get(state, `chat.chatRooms.${rid}.messages_delete`, []);

export const getChatRoomMsgDeleteSelector = createSelector(
  getChatRoomMsgDelete,
  room => room
);

export const getMessageItem = (state: GlobalState, identity: string) =>
  get(state, identity);

export const getMessageItemSelector = createSelector(
  getMessageItem,
  data => data
);

export const getActionMenuRoomPageAll = (state: GlobalState) =>
  get(state, 'chat.resourceConfig.roomPageAll.menus.itemActionMenu.items');

export const getActionMenuRoomItem = (state: GlobalState) =>
  get(state, 'chat.resourceConfig.roomItem.menus.itemActionMenu.items');

export const getRoomItem = (state: GlobalState, rid: string) =>
  get(state, `chat.entities.room.${rid}`);

export const getRoomItemSelector = createSelector(getRoomItem, data => data);

export const getOpenChatRooms = (state: GlobalState) =>
  get(state, 'chat.openRooms');

export const getOpenChatRoomsSelector = createSelector(
  getOpenChatRooms,
  data => data
);

export const getNewChatRoom = (state: GlobalState) =>
  get(state, 'state.chat.newChatRoom');

export const getActionMenuDockChat = (state: GlobalState) =>
  get(state, 'chat.resourceConfig.roomDockChat.menus.itemActionMenu.items');

export const getRooms = (state: GlobalState): AppState['entities']['room'] =>
  get(state, 'chat.entities.room', {});

export const getMessages = (state: GlobalState): MessageType =>
  get(state, 'chat.entities.message');

export const getFirstRoomSelector = (
  state: GlobalState
): RoomItemShape | null => {
  let result = null;

  const rooms: AppState['entities']['room'] = get(
    state,
    'chat.entities.room',
    {}
  );

  if (!isEmpty(rooms)) {
    result = Object.values(rooms)
      .filter(item => item.is_showed)
      .sort(
        (a, b) =>
          new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime()
      )[0];
  }

  return result;
};

export const getNotifications = (state: GlobalState) =>
  get(state, 'chat.notifications', { total_notification: 0 });
