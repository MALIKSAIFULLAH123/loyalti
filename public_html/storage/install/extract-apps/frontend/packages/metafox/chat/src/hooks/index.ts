import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import {
  getActionMenuDockChat,
  getActionMenuMessages,
  getActionMenuRoomItem,
  getActionMenuRoomPageAll,
  getChatRoomMsgDeleteSelector,
  getChatRoomSelector,
  getMessageItemSelector,
  getNewChatRoom,
  getOpenChatRoomsSelector,
  getRoomItemSelector,
  getRooms
} from '../selectors';
import { ChatRoomShape, MsgItemShape, AppState, RoomItemShape } from '../types';

export const useItemActionMessage = () => {
  return useSelector<GlobalState, any>(getActionMenuMessages);
};

export const useChatRoom = (rid: string) => {
  return useSelector<GlobalState, ChatRoomShape>(state =>
    getChatRoomSelector(state, rid)
  );
};

export const useChatRoomMsgDelete = (rid: string) => {
  return useSelector<GlobalState, ChatRoomShape>(state =>
    getChatRoomMsgDeleteSelector(state, rid)
  );
};

export function useMsgItem(identity: string): MsgItemShape | undefined {
  return useSelector<GlobalState, MsgItemShape>(state =>
    getMessageItemSelector(state, identity)
  );
}

export const useItemActionRoomPageAll = () => {
  return useSelector<GlobalState, any>(getActionMenuRoomPageAll);
};

export const useItemActionRoomItem = () => {
  return useSelector<GlobalState, any>(getActionMenuRoomItem);
};

export const useOpenChatRooms = () => {
  return useSelector<GlobalState, AppState['openRooms']>(
    getOpenChatRoomsSelector
  );
};

export function useNewChatRoom() {
  return useSelector<GlobalState, AppState['newChatRoom']>(state =>
    getNewChatRoom(state)
  );
}

export const useItemActionDockChat = () => {
  return useSelector<GlobalState, any>(getActionMenuDockChat);
};
export const useRoomItem = (rid: string) => {
  return useSelector<GlobalState, RoomItemShape>(state =>
    getRoomItemSelector(state, rid)
  );
};

export const useRooms = () => {
  return useSelector<GlobalState, any>(getRooms);
};

export { default as useReactionChat } from './useReactionChat';

export { default as useGetNotifications } from './useGetNotifications';
