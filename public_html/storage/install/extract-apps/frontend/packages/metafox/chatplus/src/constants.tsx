export const APP_NAME = 'chatplus';

export const APP_CHATGPT_BOT = 'chatgpt-bot';

export const RESOURCE_REPORT_ITEM = 'report_item';

export const MESSAGE_ROOM_ALERT_TYPES = [
  'uj',
  'ru',
  'au',
  'ul',
  'r',
  'ui',
  'user-muted',
  'user-unmuted',
  'room_changed_privacy',
  'room_changed_topic',
  'room_changed_announcement',
  'room_changed_description',
  'room-archived',
  'room-unarchived',
  'subscription-role-added',
  'subscription-role-removed',
  'room_changed_avatar',
  'room-set-read-only',
  'room-removed-read-only',
  'room_removed_announcement',
  'room_removed_description',
  'room_removed_topic'
];

export const NOT_SEEN_MESSAGE_TYPES = [
  'uj',
  'ru',
  'au',
  'ul',
  'r',
  'ui',
  'user-muted',
  'user-unmuted',
  'room_changed_privacy',
  'room_changed_topic',
  'room_changed_announcement',
  'room_changed_description',
  'room-archived',
  'room-unarchived',
  'subscription-role-added',
  'subscription-role-removed',
  'room_changed_avatar',
  'message_pinned',
  'message_unpinned',
  'room-set-read-only',
  'room-removed-read-only',
  'room_removed_announcement',
  'room_removed_description',
  'room_removed_topic'
];

export const CHAT_BUDDY = 'chat_buddy';

export const THROTTLE_SEARCH = 300;

export const CLOSE_CALL_POPUP_DELAY = 3000;

export const CHAT_ROOMS: string = 'chatplus_rooms';

export const NEW_CHAT_ROOM = 'NEW_CHAT_ROOM';

export const DockStatus = {
  expanded: 'expanded',
  collapsed: 'collapsed'
};

// 5 minutes
export const USER_GROUP_MSG_IN_MILISECONDS = 5 * 60 * 1000;

// 15 minute
export const GROUP_MSG_IN_MILI_SECONDS = 15 * 60 * 1000;

export const MEDIA_MOBILE_MAX = 768;

export const AVATAR_LINK = 'https://dev-chatplus.phpfox.us';

export const DISPLAY_LIMIT_POPOVER = 50;

export const MAX_LENGTH_NAME_GROUP = 255;

export const LIMIT_ONLINE = 20;

export const popperMenuStyles = {};

export const menuStyles = {
  overflow: 'auto',
  maxHeight: '255px',
  '&::-webkit-scrollbar': {
    height: '6px',
    width: '6px',
    background: 'transparent',
    borderRadius: '3px',
    transition: 'opacity 200ms'
  },

  /* Track */
  '&::-webkit-scrollbar-track': {
    margin: '4px 0',
    borderRadius: '3px'
  },

  /* Handle */
  '&::-webkit-scrollbar-thumb': {
    backgroundColor: 'rgba(0,0,0,.2)',
    borderRadius: '3px'
  },

  '&::-webkit-scrollbar-thumb:horizontal': {
    background: '#000',
    borderRadius: '10px'
  }
};

export const JUMP_MSG_ACTION = 'chatplus/jumpMessage';

export const LIMIT_MESSAGE_INIT_ROOM = 50;

export const THRESHOLD_SCROLL = 100;

export const SEARCH_UP = -1;

export const SEARCH_DOWN = 1;

export const MODE_UN_SEARCH = 'un_search';

export const LIMIT_SEARCH_MESSAGE = 20;

export const HIGHLIGHT_SEARCH_SECONDS = 5000;

export const URL_REGEX =
  /((https?:\/\/(www\.)?)|(www\.))[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z0-9()]{2,256}\b([^\s]*[\w@?^=%&\/~+#-])?/gim;
