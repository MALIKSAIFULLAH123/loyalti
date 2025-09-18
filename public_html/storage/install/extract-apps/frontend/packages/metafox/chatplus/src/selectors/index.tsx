/* eslint-disable array-callback-return */
import { GlobalState } from '@metafox/framework';
import { get, isEmpty, uniqBy } from 'lodash';
import { createSelector } from 'reselect';
import {
  AppState,
  BuddyItemShape,
  ChatRoomType,
  PermissionShape,
  RoomFiles,
  RoomPermissionShape,
  RoomType,
  UserShape
} from '../types';
import { createStringMatcher } from '../utils';

type Subscription = AppState['entities']['subscription'];
type Room = AppState['entities']['room'];
type MessageType = AppState['entities']['message'];

export const getPublicSettings = (state: GlobalState) =>
  get(state, 'chatplus.settings');

export const getUserPreferences = (state: GlobalState) =>
  get(state, 'chatplus.userPreferences');

export const getBuddyPanel = (state: GlobalState) =>
  get(state, 'chatplus.buddyPanel');

export const getBuddyItem = (state: GlobalState, rid: string): BuddyItemShape =>
  get(state, `chatplus.entities.buddy.${rid}`);

export const getMessageItem = (state: GlobalState, identity: string) =>
  get(state, identity);

export const getMessageItemSelector = createSelector(
  getMessageItem,
  data => data
);

export const getChatUser = (state: GlobalState) =>
  get(state, 'chatplus.session.user');

export const getFriends = (state: GlobalState): AppState['friends'] =>
  get(state, 'chatplus.friends', {});

export const getChatUserSelector = createSelector(getChatUser, user => user);

export const getChatUserItem = (state: GlobalState, userId: string) => {
  const user = get(state, `chatplus.users.${userId}`);

  if (!isEmpty(user)) return user;

  const session = getChatUser(state);

  if (session?._id === userId) return session;
};

export const getChatUserItemSelector = createSelector(
  getChatUserItem,
  user => user
);

export const getOpenChatRooms = (state: GlobalState) =>
  get(state, 'chatplus.openRooms');

export const getOpenChatRoomsSelector = createSelector(
  getOpenChatRooms,
  data => data
);

export const getChatRooms = (state: GlobalState) => {
  return get(state, 'chatplus.chatRooms');
};

export const getChatRoomsSelector = createSelector(getChatRooms, data => data);

export const getChatRoomItem = (state: GlobalState, rid: string) =>
  get(state, `chatplus.chatRooms.${rid}`);

export const getRoomItem = (state: GlobalState, rid: string) =>
  get(state, `chatplus.entities.room.${rid}`);

export const getRooms = (state: GlobalState): Room =>
  get(state, 'chatplus.entities.room');

export const getSubscriptionItem = (state: GlobalState, rid: string) =>
  get(state, `chatplus.entities.subscription.${rid}`);

export const getSubscriptions = (state: GlobalState): Subscription => {
  return get(state, 'chatplus.entities.subscription', {});
};

export const getSubscriptionsSelector = createSelector(
  getSubscriptions,
  data => data
);

export const getFilterItem = (state: GlobalState, rid: string) => {
  const chatRoom = getChatRoomItem(state, rid);

  if (!chatRoom) return;

  return get(state, `chatplus.chatRooms.${rid}.messageFilter`);
};

export const getRoomItemSelector = createSelector(getRoomItem, data => data);

export const getEntitiesMessageIdByRoomId = (
  state: GlobalState,
  rid: string
) => {
  if (!state.chatplus.entities.message) return;

  return Object.keys(state.chatplus.entities.message).filter(
    key => state.chatplus.entities.message[key].rid === rid
  );
};

export const getItemRoomSpotlight = (state: GlobalState, rid: string) => {
  if (!state.chatplus.spotlight.rooms) return;

  return Object.values(state.chatplus.spotlight.rooms).find(
    item => item._id === rid
  );
};

export const getItemRoomSpotlightSelector = createSelector(
  getItemRoomSpotlight,
  data => data
);

export const getEntitiesMessageIdByRoomIdSelector = createSelector(
  getEntitiesMessageIdByRoomId,
  data => data
);

export const getSubscriptionItemSelector = createSelector(
  getSubscriptionItem,
  data => data
);

export const getMessageFilterItemSelector = createSelector(
  getFilterItem,
  data => data
);

export const getChatRoomSelector = createSelector(
  getChatRoomItem,
  room => room
);

export const getNewChatRoom = (state: GlobalState) =>
  state.chatplus.newChatRoom;

export const getSpotlight = (state: GlobalState) => state.chatplus.spotlight;

export const getPublicSettingsSelector = createSelector(
  getPublicSettings,
  data => data
);
export const getUserPreferencesSelector = createSelector(
  getUserPreferences,
  data => data
);

function hasPermission(roles: string[], check: string[]): boolean {
  return !!roles.find(x => check.includes(x));
}

const getRoomType = (state: GlobalState, rid: string) =>
  get(state, `chatplus.entities.room.${rid}.t`);

export const getRoomFilesSelector = (
  state: GlobalState,
  rid: string
): RoomFiles => get(state, `chatplus.roomFiles.${rid}`);

const getRoles = (state: GlobalState, rid: string) => {
  const user_roles = get(state, 'chatplus.session.user.roles');
  const subscription_roles = get(
    state,
    `chatplus.entities.subscription.${rid}.roles`
  );

  return [...(user_roles || []), ...(subscription_roles || [])];
};

const getUserPermission = (state: GlobalState) =>
  get(state, 'chatplus.permissions.values');

export const getUsers = (state: GlobalState) =>
  get(state, 'chatplus.users', {});

export const getActionMenuMessages = (state: GlobalState) =>
  get(state, 'chatplus.resourceConfig.message.menus.itemActionMenu.items');

export const getActionMenuRoomPageAll = (state: GlobalState) =>
  get(state, 'chatplus.resourceConfig.roomPageAll.menus.itemActionMenu.items');

export const getActionMenuRoomDockChat = (state: GlobalState) =>
  get(state, 'chatplus.resourceConfig.roomDockChat.menus.itemActionMenu.items');

export const getActionMenuRoomItem = (state: GlobalState) =>
  get(state, 'chatplus.resourceConfig.roomItem.menus.itemActionMenu.items');

export const getActionMenuMsgFilterPopper = (state: GlobalState) =>
  get(
    state,
    'chatplus.resourceConfig.msgFilterPopperMenu.menus.itemActionMenu.items'
  );

export const getCalls = (state: GlobalState): AppState['calls'] =>
  get(state, 'chatplus.calls', {});
export const getCallItem = (state: GlobalState, callId: string) =>
  get(state, `chatplus.calls.${callId}`);

export function deriveRoomPermission(
  roomType: ChatRoomType,
  roles: string[],
  perms: PermissionShape[]
): RoomPermissionShape {
  const roleInRoom: string[] = roles ?? ['user'];
  const result = perms
    .filter(perm => hasPermission(perm.roles, roleInRoom))
    .reduce((acc, { _id }) => {
      acc[_id] = true;

      return acc;
    }, {} as RoomPermissionShape);

  result['hide-room'] = true;
  result['edit-notification'] = true;
  result['show-members'] = true;

  result['search-msg'] = true;

  result['add-members'] = result[`add-user-to-any-${roomType}-room`];
  result['start-call'] = result[`start-call-${roomType}-room`];
  result['start-video-chat'] = result[`start-video-chat-${roomType}-room`];
  result['leave-room'] = result[`leave-${roomType}`];
  result['delete-room'] = result[`delete-${roomType}`];
  result['postReadonly'] = !!result['post-readonly'];

  switch (roomType) {
    case RoomType.Public:
      result['show-members'] = true;
      break;
    case RoomType.Private:
      result['show-members'] = true;
      break;
    case RoomType.Direct:
      result['show-members'] = false;
      break;
  }

  return result;
}

export const getRoomPermissionSelector = createSelector(
  getRoomType,
  getRoles,
  getUserPermission,
  getPublicSettings,
  deriveRoomPermission
);

export const getBuddies = (state: GlobalState) => state.chatplus.entities.buddy;

export const getQuery = (
  _: GlobalState,
  query: string,
  checkSortFavorite?: boolean
) => ({ query, checkSortFavorite });

export const getSubscriptionsActive = (state: GlobalState) => {
  if (!state.chatplus.entities.subscription) return [];

  const { searchText } = getSpotlight(state);

  const subscriptions: Subscription = get(
    state,
    'chatplus.entities.subscription'
  );

  if (!isEmpty(searchText)) {
    return Object.values(subscriptions).filter(x => !x.archived);
  }

  const result = Object.values(subscriptions).filter(
    x => !x.archived && x.open
  );

  return result;
};

export const getOnlineFriendsSelector = createSelector(
  [getFriends, getQuery],
  (friends, query) => {
    if (!friends) {
      return [];
    }

    try {
      let onlineFriends = [];

      onlineFriends = Object.values(friends).filter(x => x.status);

      if (query?.query) {
        const match = createStringMatcher(query.query);

        onlineFriends = onlineFriends.filter(
          (x: UserShape) =>
            (x.username && match(x.username)) || (x.name && match(x.name))
        );
      }

      return onlineFriends.reverse();
    } catch (err) {
      // err
    }
  }
);

export const getDirectMessageSelector = createSelector(
  [getRooms, getSubscriptionsActive, getQuery],
  (rooms, subscriptionsActive, query) => {
    if (!rooms) {
      return [];
    }

    try {
      let room = [];

      const roomFilter = Object.values(rooms).filter(
        x => RoomType.Direct === x.t
      );

      subscriptionsActive.map(x => {
        Object.values(roomFilter).filter(rom => {
          if (x.rid === rom.id) room.push(rom);
        });
      });

      // sort list msg
      room = Object.values(room).sort(
        (roomA, roomB) => roomB._updatedAt.$date - roomA._updatedAt.$date
      );

      if (query?.query) {
        const match = createStringMatcher(query.query);

        room = room.filter(
          x =>
            RoomType.Direct === x.t &&
            ((x.usernames[0] && match(x.usernames[0])) ||
              (x.usernames[1] && match(x.usernames[1])))
        );
      }

      return room;
    } catch (err) {
      // err
    }
  }
);

// hidden room direct that not yet send messages
const hiddenRoom = (item, userSession) => {
  if (item?.t !== RoomType.Direct) return false;

  const isHiddenUser = item?.lastMessage?.hiddenByUserId?.find(
    user => user === userSession._id
  );

  return !item?.lastMessage || !!isHiddenUser;
};

export const getGroupChatsSelector = createSelector(
  [
    getRooms,
    getSubscriptionsActive,
    getSpotlight,
    getQuery,
    getChatUserSelector
  ],
  (rooms, subscriptionsActive, spotlight, query, userSession) => {
    let directChats = [];
    let publicGroups = [];
    let chatBot = null;
    let isFetchDone = false;

    if (!rooms) {
      return {
        directChats,
        publicGroups,
        chatBot
      };
    }

    try {
      subscriptionsActive.map(x => {
        Object.values(rooms)
          .filter(i => RoomType.Public !== i.t || x.f)
          .filter(room => {
            const hiddenItem = !query?.query && hiddenRoom(room, userSession);

            if (x.rid === room.id && !hiddenItem)
              directChats.push({
                ...room,
                f: x.f,
                targetFName: x.fname,
                targetName: x.name
              });
          });
      });

      subscriptionsActive
        .filter(sub => !sub.f)
        .map(x => {
          Object.values(rooms)
            .filter(x => RoomType.Public === x.t)
            .filter(room => {
              if (x.rid === room.id) publicGroups.push(room);
            });
        });

      // sort list msg
      directChats = directChats.sort(
        (roomA, roomB) =>
          (query.checkSortFavorite &&
            Number(roomB.f || 0) - Number(roomA.f || 0)) ||
          roomB._updatedAt.$date - roomA._updatedAt.$date
      );

      publicGroups = publicGroups.sort(
        (roomA, roomB) => roomB._updatedAt.$date - roomA._updatedAt.$date
      );

      if (query?.query) {
        const match = createStringMatcher(query.query);

        // filter data store
        directChats = directChats.filter(x => {
          if (
            RoomType.Direct === x.t &&
            ((x.targetFName && match(x.targetFName)) ||
              (x.targetName && match(x.targetName)))
          ) {
            return x;
          }

          if (
            RoomType.Private === x.t &&
            (x.fname || x.name) &&
            (match(x.fname) || match(x.name))
          ) {
            return x;
          }
        });

        publicGroups = publicGroups.filter(
          x => (x.fname || x.name) && (match(x.fname) || match(x.name))
        );

        // filter data spotlight
        if (!isEmpty(spotlight.rooms)) {
          const roomPublic = spotlight.rooms
            .filter(x => RoomType.Public === x.t)
            .map(item => ({
              ...item,
              id: item?._id || item?.id,
              no_join: true
            }));

          publicGroups = [...publicGroups, ...roomPublic];
        }

        if (!isEmpty(spotlight.users)) {
          let users = [];

          users = spotlight.users
            .map(item => ({ ...item, no_join: true, spotlight_user: true }))
            .filter(
              user =>
                !subscriptionsActive.some(
                  x => x?.other?.username === user?.username
                )
            );

          users = uniqBy(users, '_id');
          directChats = [...directChats, ...users];
        }

        publicGroups = uniqBy(publicGroups, '_id');
        directChats = uniqBy(directChats, '_id');
      }

      const indexChatBot = directChats.findIndex(item => item.isBotRoom);

      if (indexChatBot !== -1) {
        chatBot = directChats[indexChatBot];
        directChats.splice(indexChatBot, 1);
      }

      isFetchDone = true;

      return {
        directChats,
        publicGroups,
        isFetchDone,
        chatBot
      };
    } catch (err) {
      // err
    }
  }
);

export const getArchivedChatsSelector = createSelector(
  [getRooms, getQuery],
  (rooms, query) => {
    if (!rooms) {
      return [];
    }

    try {
      let archivedMessages = [];
      archivedMessages = Object.values(rooms).filter(x => x.archived);

      // sort list msg
      archivedMessages = Object.values(archivedMessages).sort(
        (roomA, roomB) => roomB._updatedAt.$date - roomA._updatedAt.$date
      );

      if (query?.query) {
        const match = createStringMatcher(query.query);

        archivedMessages = archivedMessages.filter(
          x =>
            (RoomType.Private === x.t || RoomType.Public === x.t) &&
            (x.fname || x.name) &&
            (match(x.fname) || match(x.name))
        );
      }

      return archivedMessages;
    } catch (err) {
      // err
    }
  }
);

export const getMessages = (state: GlobalState): MessageType =>
  get(state, 'chatplus.entities.message');

export const getNotifications = (state: GlobalState) =>
  get(state, 'chatplus.notifications', { unread: 0 });
