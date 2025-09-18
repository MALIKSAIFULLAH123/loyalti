import { createReducer, Draft } from '@reduxjs/toolkit';
import { findLastIndex, uniqBy } from 'lodash';
import {
  AppState,
  ChatRoomShape,
  MsgGroupShape,
  MsgItemShape,
  MsgSetShape,
  RoomItemShape
} from '../types';
import { convertTypeMessage } from '../utils';

// 5 minutes
const IDLE_TIME = 3e5;
// 15 minute
const IDLE_GROUP_TIME = 9e5;

const createMsgSetState = (msg: MsgItemShape): MsgSetShape => ({
  ts: msg.created_at,
  type: msg.type,
  user: msg.user,
  user_id: msg.user_id,
  items: [msg.id]
});

const createMsgGroupState = (msg: MsgItemShape): MsgGroupShape => {
  return {
    t0: msg.created_at,
    t1: msg.created_at,
    ts: msg.created_at,
    items: [createMsgSetState(msg)]
  };
};

const createEmptyChatRoom = (): ChatRoomShape => {
  return {
    groupIds: [],
    groups: {},
    msgCount: 0,
    msgNewest: '',
    oldest: 0,
    newest: 0,
    hasMore: true,
    searching: false,
    searchText: '',
    messageFilter: {},
    preFetchingMsg: [],
    textEditor: '',
    searchMessages: {}
  };
};

type State = AppState['chatRooms'];

type UpdateMessageAction = {
  type: 'chat/chatroom/messages';
  payload: {
    rid: string;
    messages: MsgItemShape[];
    unreadNotLoaded: number;
  };
};

const sortDate = (a: string, b: string) => {
  const date_a = new Date(a).getTime() as any;
  const date_b = new Date(b).getTime() as any;

  return parseInt(date_a, 10) - parseInt(date_b, 10);
};

export default createReducer<State>({}, builder => {
  builder.addCase('chat/init', (state: Draft<State>, action: any) => {
    const rooms: RoomItemShape[] = action.payload?.rooms;

    if (!rooms) return;

    rooms.forEach(x => {
      const id = x.id;

      if (!state[id]) {
        state[id] = createEmptyChatRoom();
      }
    });
  });

  builder.addCase('chat/room/text', (state: Draft<State>, action: any) => {
    const { rid, text } = action.payload;

    if (!rid) return;

    if (!state[rid]) {
      state[rid] = createEmptyChatRoom();
    }

    if (state[rid]) {
      state[rid].textEditor = text;
    }
  });

  builder.addCase(
    'chat/chatroom/preFetchingMsg',
    (state: Draft<State>, action: any) => {
      const {
        payload: { rid, message }
      } = action;

      if (message.idNewMsg) {
        const msgIndex = state[rid].preFetchingMsg.findIndex(
          item => item.idNewMsg === message.idNewMsg
        );

        if (msgIndex !== -1) {
          state[rid].preFetchingMsg[msgIndex].isLoading = message.isLoading;
        } else {
          state[rid].preFetchingMsg = [...state[rid].preFetchingMsg, message];
        }
      }
    }
  );

  builder.addCase('chat/chatroom/add', (state: Draft<State>, action: any) => {
    const rid: string = action.payload?.rid;

    if (!rid) return;

    if (!state[rid]) {
      state[rid] = createEmptyChatRoom();

      return;
    }
  });

  builder.addCase(
    'chat/chatroom/setMessageDelete',
    (state: Draft<State>, action: any) => {
      const { rid, message } = action.payload;

      if ('messageDeleted' !== convertTypeMessage({ msg: message })) return;

      if (!state[rid]) {
        state[rid].messages_delete = [];

        return;
      }

      state[rid].messages_delete = [
        ...(state[rid]?.messages_delete || []),
        message?.id
      ];
    }
  );

  builder.addCase(
    'chat/chatroom/messages',
    (state: Draft<State>, action: UpdateMessageAction) => {
      const {
        payload: { rid, messages: messagesDataPayload }
      } = action;

      if (!state[rid]) {
        state[rid] = createEmptyChatRoom();
      }

      state[rid].groupIds = [];
      state[rid].groups = {};
      const messagesData = uniqBy(
        [...(state[rid].messages || []), ...messagesDataPayload],
        'id'
      );

      const messages = messagesData.sort((a, b) => {
        const time_a = new Date(a.created_at).getTime();
        const time_b = new Date(b.created_at).getTime();

        return time_a - time_b;
      });

      const newest =
        new Date(messages[messages.length - 1]?.created_at).getTime() || 0;
      const oldest = new Date(messages[0]?.created_at).getTime() || 0;
      const hasMore = state[rid] ? state[rid]?.hasMore : true;
      const msgNewest = messages.map(ms => ms.id)[messages.length - 1];
      const lastMsgId = messages.map(ms => ms.id)[0];

      let prevUnixGid: number = state[rid].groupIds?.length
        ? parseInt(state[rid].groupIds[state[rid].groupIds.length - 1], 10)
        : 0;

      messages.forEach(msg => {
        // group msg per IDLE_GROUP_TIME

        const timeMsg = new Date(msg.created_at).getTime();
        let unixGid: number;

        if (prevUnixGid + IDLE_GROUP_TIME > timeMsg) {
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
          state[rid].groupIds = state[rid].groupIds.sort(sortDate);

          state[rid].newest = newest;
          state[rid].oldest = oldest;
          state[rid].hasMore = hasMore;
          state[rid].msgNewest = msgNewest;
          state[rid].lastMsgId = lastMsgId;
          state[rid].messages = messages;

          return;
        }

        let setIndex = findLastIndex(
          state[rid].groups[gid].items,
          (set: MsgSetShape, index: number) => {
            const timeSet = new Date(set.ts).getTime();
            const timeMsg = new Date(msg.created_at).getTime();

            if (index === state[rid].groups[gid].items.length - 1)
              return (
                msg.user_id === set.user_id &&
                timeMsg > timeSet &&
                timeMsg < timeSet + IDLE_TIME
              );

            return (
              msg.user_id === set.user_id && // same people
              timeMsg > timeSet && // date create > older msg
              timeMsg <
                new Date(
                  state[rid].groups[gid].items[index + 1].ts
                ).getTime() &&
              timeMsg < timeSet + IDLE_TIME
            );
          }
        );

        const setLength = state[rid].groups[gid].items.length - 1;

        if (
          setIndex !== setLength - 1 &&
          state[rid].groups[gid].items[setLength].user_id !== msg.user_id
        ) {
          setIndex = -1;
        }

        if (setIndex === -1) {
          // check if msg._id is exists.

          // not found
          // where to insert ?
          const newSet = createMsgSetState(msg);
          state[rid].groups[gid].items.push(newSet);

          state[rid].groups[gid].items.sort(
            (a, b) => new Date(a.ts).getTime() - new Date(b.ts).getTime()
          );
        } else {
          const set = state[rid].groups[gid].items[setIndex];

          if (!set.items.includes(msg.id)) {
            if (
              new Date(set.ts).getTime() < new Date(msg.created_at).getTime()
            ) {
              set.items.push(msg.id);
            } else {
              set.items.unshift(msg.id);
            }
          }
        }

        state[rid].newest = newest;
        state[rid].oldest = oldest;
        state[rid].hasMore = hasMore;
        state[rid].msgNewest = msgNewest;
        state[rid].messages = messages;
        state[rid].lastMsgId = lastMsgId;
      });
    }
  );

  builder.addCase('chat/room/toggleSearching', (state: State, action: any) => {
    const { identity: rid } = action.payload;

    if (!state[rid]) return;

    state[rid].searching = !state[rid].searching;
  });

  builder.addCase('chat/room/closeSearching', (state: State, action: any) => {
    const { identity: rid } = action.payload;

    if (!state[rid]) return;

    state[rid].searching = false;
    state[rid].messageFilter = {};
  });

  builder.addCase(
    'chat/room/deleteMessagesFilter',
    (state: State, action: any) => {
      const { identity: rid } = action.payload;

      if (!state[rid]) return;

      state[rid].messageFilter = {};
    }
  );

  builder.addCase(
    'chat/room/saveMessagesFilter',
    (state: State, action: any) => {
      const { identity: rid, data } = action.payload;

      if (!state[rid]) return;

      state[rid].messageFilter = data;
    }
  );

  builder.addCase('chat/room/delete', (state: Draft<State>, action: any) => {
    const { identity: rid } = action.payload;

    if (!state[rid]) return;

    delete state[rid];
  });

  builder.addCase(
    'chat/room/addRoomFileProgress',
    (state: State, action: any) => {
      const { rid, value, key } = action.payload;

      state[rid].roomProgress = { [key]: value };
    }
  );

  builder.addCase(
    'chat/room/updateRoomFileProgress',
    (state: State, action: any) => {
      const { rid, progress, key } = action.payload;

      if (!state[rid].roomProgress && !state[rid].roomProgress[key]) return;

      state[rid].roomProgress[key].progress = progress;
    }
  );

  builder.addCase(
    'chat/room/deleteRoomFileProgress',
    (state: State, action: any) => {
      const { rid, key } = action.payload;

      if (!state[rid].roomProgress && !state[rid].roomProgress[key]) return;

      delete state[rid].roomProgress[key];
    }
  );

  builder.addCase(
    'chat/room/endLoadmoreMessage',
    (state: State, action: any) => {
      const { rid } = action.payload;

      state[rid].endLoadmoreMessage = true;
    }
  );

  builder.addCase(
    'chat/room/search/endLoadmoreMessage',
    (state: State, action: any) => {
      const { rid, mid } = action.payload;

      if (!state[rid]?.searchMessages[mid]) return;

      state[rid].searchMessages[mid].endLoadmoreMessage = true;
    }
  );

  builder.addCase(
    'chat/room/search/endTopLoadmoreMessage',
    (state: State, action: any) => {
      const { rid, mid } = action.payload;

      if (!state[rid]?.searchMessages[mid]) return;

      state[rid].searchMessages[mid].endTopLoadmoreMessage = true;
    }
  );

  builder.addCase('chat/room/searchLoading', (state: State, action: any) => {
    const { rid, loading } = action.payload;

    if (!state[rid]) return;

    state[rid].msgSearch = {
      ...state[rid].msgSearch,
      loading
    };
  });

  builder.addCase(
    'chat/room/addSearchMessages',
    (state: State, action: any) => {
      const { rid, mid, messages: messagesDataPayload } = action.payload;

      if (!state[rid]) return;

      if (!state[rid]?.searchMessages[mid]) {
        state[rid].searchMessages[mid] = createEmptyChatRoom();
      }

      state[rid].searchMessages[mid].groupIds = [];
      state[rid].searchMessages[mid].groups = {};
      const messagesData = uniqBy(
        [
          ...(state[rid].searchMessages[mid].messages || []),
          ...messagesDataPayload
        ],
        'id'
      );

      const messages = messagesData.sort((a, b) => {
        const time_a = new Date(a.created_at).getTime();
        const time_b = new Date(b.created_at).getTime();

        return time_a - time_b;
      });

      const newest =
        new Date(messages[messages.length - 1]?.created_at).getTime() || 0;
      const oldest = new Date(messages[0]?.created_at).getTime() || 0;
      const hasMore = state[rid].searchMessages[mid]
        ? state[rid].searchMessages[mid]?.hasMore
        : true;
      const msgNewest = messages.map(ms => ms.id)[messages.length - 1];
      const lastMsgId = messages.map(ms => ms.id)[0];

      let prevUnixGid: number = state[rid].searchMessages[mid].groupIds?.length
        ? parseInt(
            state[rid].groupIds[
              state[rid].searchMessages[mid].groupIds.length - 1
            ],
            10
          )
        : 0;

      messages.forEach(msg => {
        // group msg per IDLE_GROUP_TIME

        const timeMsg = new Date(msg.created_at).getTime();
        let unixGid: number;

        if (prevUnixGid + IDLE_GROUP_TIME > timeMsg) {
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
            state[rid].searchMessages[mid].groupIds.sort(sortDate);

          state[rid].searchMessages[mid].newest = newest;
          state[rid].searchMessages[mid].oldest = oldest;
          state[rid].searchMessages[mid].hasMore = hasMore;
          state[rid].searchMessages[mid].msgNewest = msgNewest;
          state[rid].searchMessages[mid].lastMsgId = lastMsgId;
          state[rid].searchMessages[mid].messages = messages;

          return;
        }

        let setIndex = findLastIndex(
          state[rid].searchMessages[mid].groups[gid].items,
          (set: MsgSetShape, index: number) => {
            const timeSet = new Date(set.ts).getTime();
            const timeMsg = new Date(msg.created_at).getTime();

            if (
              index ===
              state[rid].searchMessages[mid].groups[gid].items.length - 1
            )
              return (
                msg.user_id === set.user_id &&
                timeMsg > timeSet &&
                timeMsg < timeSet + IDLE_TIME
              );

            return (
              msg.user_id === set.user_id && // same people
              timeMsg > timeSet && // date create > older msg
              timeMsg <
                new Date(
                  state[rid].searchMessages[mid].groups[gid].items[index + 1].ts
                ).getTime() &&
              timeMsg < timeSet + IDLE_TIME
            );
          }
        );

        const setLength =
          state[rid].searchMessages[mid].groups[gid].items.length - 1;

        if (
          setIndex !== setLength - 1 &&
          state[rid].searchMessages[mid].groups[gid].items[setLength]
            .user_id !== msg.user_id
        ) {
          setIndex = -1;
        }

        if (setIndex === -1) {
          // check if msg._id is exists.

          // not found
          // where to insert ?
          const newSet = createMsgSetState(msg);
          state[rid].searchMessages[mid].groups[gid].items.push(newSet);

          state[rid].searchMessages[mid].groups[gid].items.sort(
            (a, b) => new Date(a.ts).getTime() - new Date(b.ts).getTime()
          );
        } else {
          const set =
            state[rid].searchMessages[mid].groups[gid].items[setIndex];

          if (!set.items.includes(msg.id)) {
            if (
              new Date(set.ts).getTime() < new Date(msg.created_at).getTime()
            ) {
              set.items.push(msg.id);
            } else {
              set.items.unshift(msg.id);
            }
          }
        }

        state[rid].searchMessages[mid].newest = newest;
        state[rid].searchMessages[mid].oldest = oldest;
        state[rid].searchMessages[mid].hasMore = hasMore;
        state[rid].searchMessages[mid].msgNewest = msgNewest;
        state[rid].searchMessages[mid].messages = messages;
        state[rid].searchMessages[mid].lastMsgId = lastMsgId;
      });
    }
  );

  builder.addCase('chat/room/setMessageSearch', (state: State, action: any) => {
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
  });

  builder.addCase('chat/room/changeModeSearch', (state: State, action: any) => {
    const { rid, mode } = action.payload;

    if (!state[rid]) return;

    state[rid].msgSearch = { ...state[rid].msgSearch, mode };
  });

  builder.addCase('chat/room/clearMsgSearch', (state: State, action: any) => {
    const { rid } = action.payload;

    if (!state[rid]) return;

    state[rid].msgSearch = undefined;
  });
});
