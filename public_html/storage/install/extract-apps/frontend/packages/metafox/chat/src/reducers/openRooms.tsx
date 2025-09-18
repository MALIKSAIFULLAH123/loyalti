import produce, { Draft } from 'immer';
import { NEW_CHAT_ROOM } from '../constants';
import { AppState, OpenRoomShape } from '../types';

type Action =
  | {
      type: 'chat/openRooms/init';
      payload: { openRooms: AppState['openRooms']['values'] };
    }
  | {
      type: 'chat/openRoomPanel';
      payload: Record<string, any>;
    }
  | {
      type: 'chat/openRooms/updateValues';
      payload: Partial<AppState['openRooms']>;
    }
  | {
      type: 'chat/openRooms/toggleCollapsedPanel';
      payload: Partial<AppState['openRooms']>;
    }
  | {
      type: 'chat/closePanel';
      payload: Partial<AppState['closeRooms']>;
    }
  | {
      type: 'chat/closeAllPanel';
    }
  | {
      type: 'chat/openRooms/collapsedAllPanel';
    }
  | {
      type: 'chat/openRooms/activeRoom';
      payload: string;
    }
  | {
      type: 'chat/closeIconMsg';
    }
  | {
      type: 'chat/openRooms/minimize';
      payload: any;
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
      case 'chat/openRooms/init':
        draft.values = action.payload.openRooms || [];
        draft.closeIconMsg = false;
        break;
      case 'chat/openRoomPanel':
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
      case 'chat/openRooms/toggleCollapsedPanel':
        {
          const { payload: rid } = action;

          const found = draft.values.findIndex(x => x.rid === rid);

          if (found > -1) {
            draft.values[found].collapsed = !draft.values[found].collapsed;

            draft.values = parseMaximumRooms(draft.values, draft.newChatRoom);
          }
        }
        break;
      case 'chat/openRooms/minimize':
        {
          const { payload } = action || {};

          const identity = payload?.identity;

          if (!identity) return draft;

          const found = draft.values.findIndex(x => x.rid === identity);

          if (found > -1) {
            draft.values = draft.values.filter(x => x.rid !== identity);

            draft.values.push({ rid: identity, collapsed: true });
          }
        }
        break;
      case 'chat/openRooms/updateValues':
        draft.values = action.payload.values;
        draft.active = action.payload.active;
        break;
      case 'chat/openRooms/activeRoom': {
        const { payload: rid } = action;

        draft.active = rid;
        break;
      }
      case 'chat/closePanel':
        {
          const { payload: rid } = action;

          if (rid.identity === NEW_CHAT_ROOM) {
            draft.newChatRoom = false;
          } else {
            draft.values = draft.values.filter(x => x.rid !== rid.identity);
          }

          draft.active = undefined;
        }

        break;
      case 'chat/closeAllPanel':
        draft.values = [];
        draft.active = undefined;

        break;
      case 'chat/openRooms/collapsedAllPanel':
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
      case 'chat/closeIconMsg':
        draft.closeIconMsg = true;
        draft.newChatRoom = false;

        break;
      default:
        return draft;
    }
  },
  {
    values: [],
    active: '',
    newChatRoom: false,
    closeIconMsg: false,
    chatRoom: false
  }
);
