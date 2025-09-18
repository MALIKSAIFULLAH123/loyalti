import {
  AppState,
  ChatRoomShape,
  RoomFiles,
  RoomItemShape,
  RoomType,
  SubscriptionItemShape,
  UserShape
} from '@metafox/chatplus/types';
import { GlobalState } from '@metafox/framework';
import { isArray } from 'lodash';
import { useSelector } from 'react-redux';
import {
  getActionMenuMessages,
  getActionMenuMsgFilterPopper,
  getActionMenuRoomDockChat,
  getActionMenuRoomItem,
  getActionMenuRoomPageAll,
  getBuddyPanel,
  getCallItem,
  getCalls,
  getChatRoomSelector,
  getChatRoomsSelector,
  getChatUserItemSelector,
  getChatUserSelector,
  getGroupChatsSelector,
  getMessageFilterItemSelector,
  getOpenChatRoomsSelector,
  getPublicSettingsSelector,
  getRoomFilesSelector,
  getRoomItemSelector,
  getRoomPermissionSelector,
  getSubscriptionItemSelector,
  getUserPreferencesSelector,
  getUsers
} from '../selectors';
import { RoomPermissionShape } from '../types';
import React from 'react';
import { MessagesContext } from '../context';

export { default as useMessageContainer } from './useMessageContainer';
export { default as useMsgItem } from './useMsgItem';
export { default as useNewChatRoom } from './useNewChatRoom';
export { default as useReactionChat } from './useReactionChat';
export { default as useGetNotifications } from './useGetNotifications';

export function useRoomPermission(rid: string) {
  return useSelector<GlobalState, RoomPermissionShape>(state =>
    getRoomPermissionSelector(state, rid)
  );
}

export function usePublicSettings() {
  return useSelector<GlobalState, AppState['settings']>(
    getPublicSettingsSelector
  );
}

export function useUserPreferences() {
  return useSelector<GlobalState, AppState['userPreferences']>(
    getUserPreferencesSelector
  );
}

export function useRoomFiles(rid: string) {
  return useSelector<GlobalState, RoomFiles>(state =>
    getRoomFilesSelector(state, rid)
  );
}

export const useSessionUser = () => {
  return useSelector<GlobalState, AppState['session']['user']>(
    getChatUserSelector
  );
};

export const useOpenChatRooms = () => {
  return useSelector<GlobalState, AppState['openRooms']>(
    getOpenChatRoomsSelector
  );
};

export const useChatRooms = () => {
  return useSelector<GlobalState, AppState['chatRooms']>(getChatRoomsSelector);
};

export const useChatRoom = (rid: string) => {
  return useSelector<GlobalState, ChatRoomShape>(state =>
    getChatRoomSelector(state, rid)
  );
};

export const useChatUserItem = (userId: string) => {
  return useSelector<GlobalState, UserShape>(state =>
    getChatUserItemSelector(state, userId)
  );
};

export const useRoomItem = (rid: string) => {
  return useSelector<GlobalState, RoomItemShape>(state =>
    getRoomItemSelector(state, rid)
  );
};

export const useSubscriptionItem = (rid: string) => {
  return useSelector<GlobalState, SubscriptionItemShape>(state =>
    getSubscriptionItemSelector(state, rid)
  );
};

export const useMessageFilterItem = (rid: string) => {
  return useSelector<GlobalState, any>(state =>
    getMessageFilterItemSelector(state, rid)
  );
};

export const useBuddyPanel = () => {
  return useSelector<GlobalState, AppState['buddyPanel']>(getBuddyPanel);
};

export const useItemActionMessage = () => {
  return useSelector<GlobalState, any>(getActionMenuMessages);
};
export const useItemActionRoomPageAll = () => {
  return useSelector<GlobalState, any>(getActionMenuRoomPageAll);
};

export const useItemMenuMsgFilterPopper = () => {
  return useSelector<GlobalState, any>(getActionMenuMsgFilterPopper);
};

export const useItemActionRoomDockChat = () => {
  return useSelector<GlobalState, any>(getActionMenuRoomDockChat);
};

export const useItemActionRoomItem = () => {
  return useSelector<GlobalState, any>(getActionMenuRoomItem);
};

export const useCalls = () => {
  return useSelector<GlobalState, AppState['calls']>(getCalls);
};
export const useCallItem = (callId: string) => {
  return useSelector<GlobalState, any>(state => getCallItem(state, callId));
};

export const useFirstRoom = () => {
  let room: RoomItemShape = null;
  const { directChats, publicGroups, chatBot } = useSelector<GlobalState, any>(
    state => getGroupChatsSelector(state, '', true)
  );

  if (chatBot || directChats.length || publicGroups.length) {
    room = chatBot || directChats[0] || publicGroups[0];
  }

  return room;
};

export const useUsers = () => {
  return useSelector<GlobalState, UserShape[]>(getUsers);
};

export const useIsSelfChat = (rid: string) => {
  const room = useRoomItem(rid);
  const user = useSessionUser();

  if (!room) return false;

  if (room?.isBotRoom) return true;

  let isSelfChat = false;

  if (
    room?.t === RoomType.Direct &&
    user &&
    isArray(room?.uids) &&
    room?.usersCount === 1
  ) {
    isSelfChat = room?.uids?.includes(user?._id);
  }

  return isSelfChat;
};

export default function useMessagesContext(): any {
  return React.useContext(MessagesContext);
}
