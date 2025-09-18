import produce, { Draft } from 'immer';
import { NEW_CHAT_ROOM } from '../constants';
import { AppState, OpenRoomShape } from '../types';

type Action =
  | {
      type: 'chatplus/init';
      payload: { openRooms: AppState['openRooms']['values'] };
    }
  | {
      type: 'chatplus/openRooms/addRoomToChatDock';
      payload: Record<string, any>;
    }
  | {
      type: 'chatplus/openRooms/updateValues';
      payload: Partial<AppState['openRooms']>;
    }
  | {
      type: 'chatplus/openRooms/togglePanel';
      payload: Partial<AppState['openRooms']>;
    }
  | {
      type: 'chatplus/openRooms/minimize';
      payload: Partial<AppState['openRooms']>;
    }
  | {
      type: 'chatplus/closePanel';
      payload: Partial<AppState['closeRooms']>;
    }
  | {
      type: 'chatplus/closeAllPanel';
    }
  | {
      type: 'chatplus/openRooms/toggleAllPanel';
    }
  | {
      type: 'chatplus/openRooms/activeRoom';
      payload: string;
    }
  | {
      type: 'chatplus/closeIconMsg';
    }
  | {
      type: 'chatplus/openIconMsg';
    };

const MAX_ROOMS = 3;

const parseMaximumRooms = (data: OpenRoomShape[], hasNewRoom: boolean) => {
  if (!data?.length) return [];

  const listRoom = data.filter(x => !x.collapsed);
  const total = hasNewRoom ? listRoom.length + 1 : listRoom.length;

  if (total > MAX_ROOMS) {
    const indexChunk = total - MAX_ROOMS;
    const ridsShouldMinimize = listRoom.slice(0, indexChunk).map(x => x.rid);
    data = data.filter(x => !ridsShouldMinimize.includes(x.rid));
    data.push(...ridsShouldMinimize.map(x => ({ rid: x, collapsed: true })));
  }

  return data;
};

export default produce(
  (draft: Draft<AppState['openRooms']>, action: Action) => {
    switch (action.type) {
      case 'chatplus/init':
        draft.values = action.payload.openRooms || [];
        draft.init = true;
        draft.closeIconMsg = false;

        break;
      case 'chatplus/openRooms/addRoomToChatDock':
        {
          const { payload } = action;

          if (!payload) return;

          const { rid, text } = payload || {};

          const found = draft.values.findIndex(x => x.rid === rid);
          draft.closeIconMsg = false;

          if (rid === NEW_CHAT_ROOM) {
            draft.newChatRoom = true;
            draft.active = NEW_CHAT_ROOM;
          } else if (found > -1) {
            draft.values[found].collapsed = false;
            draft.values[found].text = text;
            draft.active = draft.values[found].rid;
          } else {
            draft.values.push({ rid, collapsed: false, text });
            draft.active = rid;
          }

          draft.values = parseMaximumRooms(draft.values, draft.newChatRoom);
        }
        break;
      case 'chatplus/openRooms/togglePanel':
        {
          const { payload: rid } = action;

          const found = draft.values.findIndex(x => x.rid === rid);

          if (found > -1) {
            draft.values[found].collapsed = !draft.values[found].collapsed;

            draft.values = parseMaximumRooms(draft.values, draft.newChatRoom);
          }
        }
        break;
      case 'chatplus/openRooms/minimize':
        {
          const { payload: rid } = action;

          const identity = rid?.identity;

          if (!identity) return draft;

          const found = draft.values.findIndex(x => x.rid === identity);

          if (found > -1) {
            draft.values = draft.values.filter(x => x.rid !== identity);

            draft.values.push({ rid: identity, collapsed: true });
          }
        }
        break;
      case 'chatplus/openRooms/updateValues':
        draft.values = action.payload.values;
        draft.active = action.payload.active;
        break;
      case 'chatplus/openRooms/activeRoom': {
        const { payload: rid } = action;

        draft.active = rid;
        break;
      }
      case 'chatplus/closePanel':
        {
          const { payload: rid } = action;

          const identity = rid?.identity;

          if (!identity) return draft;

          if (identity === NEW_CHAT_ROOM) {
            draft.newChatRoom = false;
          } else {
            draft.values = draft.values.filter(x => x.rid !== identity);
          }

          draft.active = undefined;
        }
        break;
      case 'chatplus/closeAllPanel':
        draft.values = [];
        draft.active = undefined;

        break;
      case 'chatplus/openRooms/toggleAllPanel':
        {
          const values = draft.values.map(item => {
            if (!item.collapsed) item.collapsed = true;

            return item;
          });

          if (values.length) {
            draft.values = values;
            draft.active = undefined;
          }
        }
        break;
      case 'chatplus/closeIconMsg':
        draft.closeIconMsg = true;
        draft.newChatRoom = false;

        break;
      case 'chatplus/openIconMsg':
        draft.closeIconMsg = false;
        break;
      default:
        return draft;
    }
  },
  {
    values: [],
    active: '',
    newChatRoom: false
  }
);
