import { createReducer, Draft } from '@reduxjs/toolkit';
import { findLastIndex, isEmpty, uniqBy } from 'lodash';
import {
  GROUP_MSG_IN_MILI_SECONDS,
  USER_GROUP_MSG_IN_MILISECONDS
} from '../constants';
import {
  AppState,
  ChatRoomShape,
  InitResultShape,
  MsgGroupShape,
  MsgItemShape,
  MsgSetShape
} from '../types';

const createMsgSetState = (msg: MsgItemShape): MsgSetShape => ({
  ts: msg.ts,
  t: msg.t,
  system: msg.system,
  groupable: msg.groupable,
  u: msg.u,
  items: [msg._id]
});

const createMsgGroupState = (msg: MsgItemShape): MsgGroupShape => {
  return {
    t0: msg.ts.$date,
    t1: msg.ts.$date,
    ts: msg.ts,
    items: [createMsgSetState(msg)]
  };
};

const createEmptyChatRoom = (): ChatRoomShape => {
  return {
    groupIds: [],
    members: [],
    groups: {},
    msgCount: 0,
    msgNewest: '',
    oldest: 0,
    newest: 0,
    hasMore: true,
    collapsed: false,
    searching: false,
    addNewMembers: false,
    searchText: '',
    messageFilter: {},
    pinned: false,
    starred: false,
    resultSearchMembers: [],
    textEditor: '',
    searchMessages: {}
  };
};

type State = AppState['chatRooms'];

type UpdateMessageAction = {
  type: 'chatplus/room/messages';
  payload: {
    rid: string;
    messages: MsgItemShape[];
    unreadNotLoaded: number;
  };
};

const sortTime = (a: string, b: string) => {
  return parseInt(a, 10) - parseInt(b, 10);
};

export default createReducer<State>({}, builder => {
  builder.addCase('chatplus/init', (state: Draft<State>, action: any) => {
    const rooms: InitResultShape['rooms'] = action.payload?.rooms;
    const subscriptions: InitResultShape['subscriptions'] =
      action.payload?.subscriptions;

    if (!rooms) return;

    rooms.forEach(x => {
      const id = x._id;

      if (!state[id]) {
        state[id] = createEmptyChatRoom();
      }
    });

    subscriptions.forEach(x => {
      const id = x.rid;

      if (!state[id]) {
        state[id] = createEmptyChatRoom();
      }
    });
  });
  builder.addCase('chatplus/room/text', (state: Draft<State>, action: any) => {
    const { rid, text } = action.payload;

    if (!rid && !state[rid]) return;

    if (state[rid]) {
      state[rid].textEditor = text;

      return;
    }
  });

  builder.addCase('chatplus/room/add', (state: Draft<State>, action: any) => {
    const room: InitResultShape['room'] = action.payload?.room;
    const subscription: InitResultShape['subscription'] =
      action.payload?.subscription;

    if (!room && !subscription) return;

    const roomId = room?.id || subscription?.rid;

    if (roomId && !state?.[roomId]) {
      state[roomId] = createEmptyChatRoom();
    }
  });

  builder.addCase(
    'chatplus/room/delete',
    (state: Draft<State>, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      delete state[rid];
    }
  );

  builder.addCase(
    'chatplus/room/messages',
    (state: Draft<State>, action: UpdateMessageAction) => {
      const {
        payload: { rid, messages: messagesDataPayload }
      } = action;

      if (!state[rid]) return;

      state[rid].groupIds = [];
      state[rid].groups = {};
      const messagesData = uniqBy(
        [...(state[rid].messages || []), ...messagesDataPayload],
        '_id'
      );
      const messages = messagesData.sort((a, b) => {
        return a.ts.$date - b.ts.$date;
      });

      const newest = messages[messages.length - 1]?.ts?.$date || 0;
      const oldest = messages[0]?.ts?.$date || 0;
      const hasMore = state[rid] ? state[rid]?.hasMore : true;
      const msgNewest =
        state[messages.map(ms => ms._id)[messages.length - 1]]?.msg;
      let prevUnixGid: number = state[rid].groupIds?.length
        ? parseInt(state[rid].groupIds[state[rid].groupIds.length - 1], 10)
        : 0;

      messages.forEach((msg, index) => {
        // group msg per GROUP_MSG_IN_MILI_SECONDS

        const timeMsg = msg.ts.$date;
        let unixGid: number;

        if (prevUnixGid + GROUP_MSG_IN_MILI_SECONDS > timeMsg) {
          // keep old group
          unixGid = prevUnixGid;
        } else {
          // make new group
          unixGid = timeMsg;
          prevUnixGid = timeMsg;
        }

        // convert to key string
        const gid = `${unixGid}`;

        if (!state[rid].groups[gid]) {
          state[rid].groups[gid] = createMsgGroupState(msg);
          state[rid].groupIds.push(gid);
          state[rid].groupIds = state[rid].groupIds.sort(sortTime);

          state[rid].newest = newest;
          state[rid].oldest = oldest;
          state[rid].hasMore = hasMore;
          state[rid].msgNewest = msgNewest;
          state[rid].messages = messages;

          return;
        }

        let setIndex = findLastIndex(
          state[rid].groups[gid].items,
          (set: MsgSetShape, index: number) => {
            if (index === state[rid].groups[gid].items.length - 1)
              return (
                !set.t &&
                !msg.t &&
                !msg.system &&
                !set.system &&
                !set.groupable !== false &&
                msg.groupable !== false &&
                msg.u._id === set.u._id &&
                msg.ts.$date > set.ts.$date &&
                msg.ts.$date < set.ts.$date + USER_GROUP_MSG_IN_MILISECONDS
              );

            return (
              !set.t &&
              !msg.t &&
              !msg.system &&
              !set.system &&
              !set.groupable !== false &&
              msg.groupable !== false &&
              msg.u._id === set.u._id && // same people
              msg.ts.$date > set.ts.$date && // date create > older msg
              msg.ts.$date < state[rid].groups[gid].items[index + 1].ts.$date &&
              msg.ts.$date < set.ts.$date + USER_GROUP_MSG_IN_MILISECONDS
            );
          }
        );

        const setLength = state[rid].groups[gid].items.length - 1;

        if (
          setIndex !== setLength - 1 &&
          state[rid].groups[gid].items[setLength].u._id !== msg.u._id
        )
          setIndex = -1;

        if (setIndex === -1) {
          // check if msg._id is exists.

          // not found
          // where to insert ?
          const newSet = createMsgSetState(msg);
          state[rid].groups[gid].items.push(newSet);

          state[rid].groups[gid].items.sort((a, b) => a.ts.$date - b.ts.$date);
        } else {
          // if(set.ts.$date < msg.ts.$date && )

          const set = state[rid].groups[gid].items[setIndex];

          if (!set.items.includes(msg._id)) {
            if (set.ts.$date < msg.ts.$date) {
              set.items.push(msg._id);
            } else {
              set.items.unshift(msg._id);
            }
          }
        }

        state[rid].newest = newest;
        state[rid].oldest = oldest;
        state[rid].hasMore = hasMore;
        state[rid].msgNewest = msgNewest;
        state[rid].messages = messages;
      });
    }
  );

  builder.addCase(
    'chatplus/room/toggleSearching',
    (state: State, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      state[rid].searching = !state[rid].searching;

      // close closeSearchUser, addNewMembers
      state[rid].addNewMembers = false;
      state[rid].resultSearchMembers = [];
      state[rid].pinned = false;
      state[rid].starred = false;
      state[rid].messageFilter = {};
    }
  );

  builder.addCase(
    'chatplus/room/toggleAddNewMembers',
    (state: State, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      state[rid].addNewMembers = !state[rid].addNewMembers;

      // close search message
      state[rid].searching = false;
      state[rid].messageFilter = {};
    }
  );

  // search user results.
  builder.addCase(
    'chatplus/room/searchUser/FULFILL',
    (state: State, action: any) => {
      const { rid, data } = action.payload;

      if (!state[rid] && data.users) return;

      state[rid].resultSearchMembers = data.users;
    }
  );

  // clear search user results.
  builder.addCase(
    'chatplus/room/clearSearchUser',
    (state: State, action: any) => {
      const { rid } = action.payload;

      if (!state[rid]) return;

      state[rid].resultSearchMembers = [];
    }
  );

  builder.addCase(
    'chatplus/room/closeSearchUser',
    (state: State, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      state[rid].addNewMembers = false;
      state[rid].resultSearchMembers = [];
    }
  );

  builder.addCase(
    'chatplus/room/closeSearching',
    (state: State, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      state[rid].searching = false;
      state[rid].messageFilter = {};
    }
  );

  builder.addCase(
    'chatplus/room/openSearching',
    (state: State, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      state[rid].searching = true;

      // close closeSearchUser, addNewMembers
      state[rid].addNewMembers = false;
      state[rid].resultSearchMembers = [];
      state[rid].pinned = false;
      state[rid].starred = false;
      state[rid].messageFilter = {};
    }
  );

  builder.addCase(
    'chatplus/room/deleteMessagesFilter',
    (state: State, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      state[rid].messageFilter = {};
    }
  );

  builder.addCase(
    'chatplus/room/deleteItemMessagesFilter',
    (state: State, action: any) => {
      const { rid, id_message } = action.payload;

      if (
        !state[rid] &&
        isEmpty(state[rid]?.messageFilter) &&
        isEmpty(state[rid]?.messageFilter[id_message])
      )
        return;

      delete state[rid].messageFilter[id_message];
    }
  );

  builder.addCase(
    'chatplus/room/saveMessagesFilter',
    (state: State, action: any) => {
      const { identity: rid, data } = action.payload;

      if (!state[rid]) return;

      state[rid].messageFilter = data;
    }
  );

  builder.addCase('chatplus/room/togglePanel', (state: State, action: any) => {
    const { identity: rid } = action.payload;

    if (!state[rid]) return;

    state[rid].collapsed = !state[rid].collapsed;
  });

  // expanding collapsed room
  builder.addCase('chatplus/room/openBuddy', (state: State, action: any) => {
    const rid = action.payload.rid;

    if (!state[rid]) return;

    if (!state[rid].collapsed) return;

    state[rid].collapsed = false;
  });

  builder.addCase(
    'chatplus/room/starredMessages',
    (state: State, action: any) => {
      const rid = action.payload.identity;

      if (!state[rid]) return;

      state[rid].searching = true;
      state[rid].starred = !state[rid].starred;
      state[rid].messageFilter = {};

      if (state[rid].starred) state[rid].pinned = false;
    }
  );

  builder.addCase(
    'chatplus/room/pinnedMessages',
    (state: State, action: any) => {
      const rid = action.payload.identity;

      if (!state[rid]) return;

      state[rid].searching = true;
      state[rid].pinned = !state[rid].pinned;
      state[rid].messageFilter = {};

      if (state[rid].pinned) state[rid].starred = false;
    }
  );

  builder.addCase(
    'chatplus/room/resetFilterPinnedStarred',
    (state: State, action: any) => {
      const rid = action.payload.identity;

      if (!state[rid]) return;

      state[rid].pinned = false;

      state[rid].starred = false;

      state[rid].messageFilter = {};
    }
  );

  builder.addCase(
    'chatplus/room/addRoomFileProgress',
    (state: State, action: any) => {
      const { rid, value, key } = action.payload;

      state[rid].roomProgress = {
        ...(state[rid].roomProgress || {}),
        [key]: value
      };
    }
  );

  builder.addCase(
    'chatplus/room/updateRoomFileProgress',
    (state: State, action: any) => {
      const { rid, progress, key } = action.payload;

      if (!state[rid].roomProgress || !state[rid].roomProgress[key]) return;

      state[rid].roomProgress[key].progress = progress;
    }
  );

  builder.addCase(
    'chatplus/room/endLoadmoreMessage',
    (state: State, action: any) => {
      const { rid } = action.payload;

      state[rid].endLoadmoreMessage = true;
    }
  );

  builder.addCase(
    'chatplus/room/deleteRoomFileProgress',
    (state: State, action: any) => {
      const { rid, key } = action.payload;

      if (!state[rid].roomProgress && !state[rid].roomProgress[key]) return;

      delete state[rid].roomProgress[key];
    }
  );

  builder.addCase(
    'chatplus/room/search/endLoadmoreMessage',
    (state: State, action: any) => {
      const { rid, mid } = action.payload;

      if (!state[rid]?.searchMessages[mid]) return;

      state[rid].searchMessages[mid].endLoadmoreMessage = true;
    }
  );

  builder.addCase(
    'chatplus/room/search/endTopLoadmoreMessage',
    (state: State, action: any) => {
      const { rid, mid } = action.payload;

      if (!state[rid]?.searchMessages[mid]) return;

      state[rid].searchMessages[mid].endTopLoadmoreMessage = true;
    }
  );

  builder.addCase(
    'chatplus/room/clearMsgSearch',
    (state: State, action: any) => {
      const { rid } = action.payload;

      if (!state[rid]) return;

      state[rid].msgSearch = undefined;
    }
  );

  builder.addCase(
    'chatplus/room/clearMQuoteSearch',
    (state: State, action: any) => {
      const { rid } = action.payload;

      if (!state[rid] && !state[rid]?.msgSearch?.mQuoteId) return;

      state[rid].msgSearch = {
        ...state[rid].msgSearch,
        mQuoteId: undefined
      };
    }
  );

  builder.addCase(
    'chatplus/room/setMessageSearch',
    (state: State, action: any) => {
      const { rid, mid, slot, mode, ...rest } = action.payload;

      if (!state[rid]) return;

      if (mode === 'quote') {
        state[rid].msgSearch = {
          ...state[rid].msgSearch,
          slot: slot || 0,
          mode,
          mQuoteId: mid,
          ...rest
        };

        return;
      }

      state[rid].msgSearch = {
        id: mid,
        slot: slot || 0,
        mode,
        ...rest
      };
    }
  );

  builder.addCase(
    'chatplus/room/searchLoading',
    (state: State, action: any) => {
      const { rid, loading } = action.payload;

      if (!state[rid]) return;

      state[rid].msgSearch = {
        ...state[rid].msgSearch,
        loading
      };
    }
  );

  builder.addCase(
    'chatplus/room/searchChangePosition',
    (state: State, action: any) => {
      const { rid, mid, type: indexChanged } = action.payload;

      if (!indexChanged && !state[rid]) return;

      if (state[rid].msgSearch.id !== mid) return;

      const newPosition = state[rid].msgSearch.slot + indexChanged;

      const id = state[rid].msgSearch.msgIds?.[newPosition];

      if (!id) return;

      state[rid].msgSearch = {
        ...state[rid].msgSearch,
        id,
        slot: newPosition,
        mQuoteId: undefined,
        mode: undefined
      };
    }
  );

  builder.addCase(
    'chatplus/room/changeModeSearch',
    (state: State, action: any) => {
      const { rid, mode } = action.payload;

      if (!state[rid]) return;

      state[rid].msgSearch = { ...state[rid].msgSearch, mode };
    }
  );

  builder.addCase(
    'chatplus/room/addSearchMessages',
    (state: State, action: any) => {
      const { rid, mid, messages: messagesDataPayload } = action.payload;

      if (!state[rid]) return;

      if (!state[rid].searchMessages[mid]) {
        state[rid].searchMessages[mid] = createEmptyChatRoom();
      }

      state[rid].searchMessages[mid].groupIds = [];
      state[rid].searchMessages[mid].groups = {};
      const messagesData = uniqBy(
        [
          ...(state[rid].searchMessages[mid].messages || []),
          ...messagesDataPayload
        ],
        '_id'
      );
      const messages = messagesData.sort((a, b) => {
        return a.ts.$date - b.ts.$date;
      });

      const newest = messages[messages.length - 1]?.id || 0;
      const oldest = messages[0]?.id || 0;
      const hasMore = state[rid].searchMessages[mid]
        ? state[rid].searchMessages[mid]?.hasMore
        : true;
      const msgNewest =
        state[messages.map(ms => ms._id)[messages.length - 1]]?.msg;
      let prevUnixGid: number = state[rid].searchMessages[mid].groupIds?.length
        ? parseInt(
            state[rid].groupIds[
              state[rid].searchMessages[mid].groupIds.length - 1
            ],
            10
          )
        : 0;

      messages.forEach((msg, index) => {
        // group msg per GROUP_MSG_IN_MILI_SECONDS

        const timeMsg = msg.ts.$date;
        let unixGid: number;

        if (prevUnixGid + GROUP_MSG_IN_MILI_SECONDS > timeMsg) {
          // keep old group
          unixGid = prevUnixGid;
        } else {
          // make new group
          unixGid = timeMsg;
          prevUnixGid = timeMsg;
        }

        // convert to key string
        const gid = `${unixGid}`;

        if (!state[rid].searchMessages[mid].groups[gid]) {
          state[rid].searchMessages[mid].groups[gid] = createMsgGroupState(msg);
          state[rid].searchMessages[mid].groupIds.push(gid);
          state[rid].searchMessages[mid].groupIds =
            state[rid].searchMessages[mid].groupIds.sort(sortTime);

          state[rid].searchMessages[mid].newest = newest;
          state[rid].searchMessages[mid].oldest = oldest;
          state[rid].searchMessages[mid].hasMore = hasMore;
          state[rid].searchMessages[mid].msgNewest = msgNewest;
          state[rid].searchMessages[mid].messages = messages;

          return;
        }

        let setIndex = findLastIndex(
          state[rid].searchMessages[mid].groups[gid].items,
          (set: MsgSetShape, index: number) => {
            if (
              index ===
              state[rid].searchMessages[mid].groups[gid].items.length - 1
            )
              return (
                !set.t &&
                !msg.t &&
                !msg.system &&
                !set.system &&
                !set.groupable !== false &&
                msg.groupable !== false &&
                msg.u._id === set.u._id &&
                msg.ts.$date > set.ts.$date &&
                msg.ts.$date < set.ts.$date + USER_GROUP_MSG_IN_MILISECONDS
              );

            return (
              !set.t &&
              !msg.t &&
              !msg.system &&
              !set.system &&
              !set.groupable !== false &&
              msg.groupable !== false &&
              msg.u._id === set.u._id && // same people
              msg.ts.$date > set.ts.$date && // date create > older msg
              msg.ts.$date <
                state[rid].searchMessages[mid].groups[gid].items[index + 1].ts
                  .$date &&
              msg.ts.$date < set.ts.$date + USER_GROUP_MSG_IN_MILISECONDS
            );
          }
        );

        const setLength =
          state[rid].searchMessages[mid].groups[gid].items.length - 1;

        if (
          setIndex !== setLength - 1 &&
          state[rid].searchMessages[mid].groups[gid].items[setLength].u._id !==
            msg.u._id
        )
          setIndex = -1;

        if (setIndex === -1) {
          // check if msg._id is exists.

          // not found
          // where to insert ?
          const newSet = createMsgSetState(msg);
          state[rid].searchMessages[mid].groups[gid].items.push(newSet);

          state[rid].searchMessages[mid].groups[gid].items.sort(
            (a, b) => a.ts.$date - b.ts.$date
          );
        } else {
          // if(set.ts.$date < msg.ts.$date && )

          const set =
            state[rid].searchMessages[mid].groups[gid].items[setIndex];

          if (!set.items.includes(msg._id)) {
            if (set.ts.$date < msg.ts.$date) {
              set.items.push(msg._id);
            } else {
              set.items.unshift(msg._id);
            }
          }
        }

        state[rid].searchMessages[mid].newest = newest;
        state[rid].searchMessages[mid].oldest = oldest;
        state[rid].searchMessages[mid].hasMore = hasMore;
        state[rid].searchMessages[mid].msgNewest = msgNewest;
        state[rid].searchMessages[mid].messages = messages;
      });
    }
  );
});
