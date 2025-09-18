import moment from 'moment';
import {
  APP_NAME,
  MESSAGE_ROOM_ALERT_TYPES,
  NOT_SEEN_MESSAGE_TYPES
} from './constants';
import {
  ChatUserStatus,
  MsgContentType,
  MsgItemShape,
  RoomItemShape,
  SubscriptionItemShape,
  UserStatusType
} from './types';
import { isString } from 'lodash';

export const countAttachmentImages = (attachments: any) => {
  return attachments?.length
    ? attachments.filter(item => item.image_url).length
    : 0;
};

export const formatBytes = (bytes: number, decimals: number = 2) => {
  if (bytes === 0) return '0 KB';

  try {
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
  } catch (err) {
    return '0 KB';
  }
};

export const convertDateTime = (date: any, formatProp = 'lll') => {
  if (!date) {
    return null;
  }

  const dateTime = new Date(date);
  const today = new Date();
  let format = formatProp;

  const _date = moment(date);

  if (!_date.isValid()) return '';

  if (today.setHours(0, 0, 0, 0) == dateTime.setHours(0, 0, 0, 0)) {
    format = 'LT';
  }

  return _date.format(format);
};

export const convertTimeActive = (date: any) => {
  if (!date) {
    return null;
  }

  const isValidDate = moment(date).isValid();
  const dateTime = moment(date);

  if (!isValidDate) return null;

  const result = dateTime.fromNow(true);

  if (result === 'Now') {
    return '1m';
  } else {
    return result;
  }
};

export const triggerClick = (
  url: string,
  targetBlank?: Boolean,
  isDownload: boolean = false
) => {
  let href = url;

  if (!href) {
    return null;
  }

  if (isDownload) {
    href = `${href}&download=1`;
  }

  // let hrefParse = settings.siteUrl + '/' + href;

  const link = document.createElement('a', {});
  link.setAttribute('href', href);
  link.setAttribute('style', 'visibility:none');

  if (targetBlank) {
    link.setAttribute('target', '_blank');
  }

  document.body.appendChild(link);

  link.click();
  setTimeout(() => {
    document.body.removeChild(link);
  }, 1e3);
};

export const filterImageAttachment = (attachments: any) => {
  const data =
    attachments && attachments.length
      ? attachments.filter(item => item.image_url)
      : [];
  const countImage = data.length > 0 ? data.length : 0;

  return {
    count: countImage,
    data
  };
};

export const isNotSeenMsg = (t: string) => NOT_SEEN_MESSAGE_TYPES.includes(t);

export const isAlertMsg = (t: string) => MESSAGE_ROOM_ALERT_TYPES.includes(t);

export const normalizeMsgContentType = (msgType: string): MsgContentType => {
  switch (true) {
    case ['au', 'ru', 'ul', 'r', 'uj', 'wm'].includes(msgType):
    case /^user_/.test(msgType):
    case /^room_/.test(msgType):
    case /^subscription/.test(msgType):
      return 'system';
    case /end_(audio|video)_call_(p|c)/.test(msgType):
      return 'groupCallEnded';
    case /(\w+)_(audio|video)_call_(p|c)/.test(msgType):
      return 'groupCall';
    case /(end|miss)_(audio|video)_call_d/.test(msgType):
      return 'directChatCallEnded';
    case /(\w+)_(video|audio)_call_d/.test(msgType):
      return 'messageEmpty';
    case ['message_pinned', 'message_unpinned'].includes(msgType):
      return 'messagePinned';
    case msgType === 'deleted' || msgType === 'rm':
      return 'messageDeleted';
    default:
      return 'standard';
  }
};

export const normalizeMsgItem = (msg: MsgItemShape) => {
  if (!msg) return msg;

  msg.id = msg._id;
  msg.module_name = APP_NAME;
  msg.resource_name = 'message';
  msg.msgType = msg.deleted
    ? 'deleted'
    : msg.t
    ? msg.t.replace(/-/g, '_')
    : '___';

  msg.msgContentType = normalizeMsgContentType(msg.msgType);

  msg.msg = msg?.msgRaw || msg?.msg;

  if (!msg.reactions) msg.reactions = undefined;
};

export const normalizeRoomItem = (room: RoomItemShape) => {
  if (!room) return room;

  room.id = room._id;
  room.module_name = APP_NAME;
  room.resource_name = 'room';
};

export const normalizeSubscriptionItem = (sub: SubscriptionItemShape) => {
  if (!sub) return sub;

  sub.id = sub._id;
  sub.module_name = APP_NAME;
  sub.resource_name = 'subscription';

  if (!sub.blocker) sub.blocker = undefined;

  if (!sub.blocked) sub.blocked = undefined;

  if (!sub.metafoxBlocker) sub.metafoxBlocker = undefined;

  if (!sub.metafoxBlocked) sub.metafoxBlocked = undefined;
};

export const createStringMatcher = (find: string) => {
  const reg = new RegExp(
    String(find).replace(/[\\^$*+?.()|[\]{}]/g, '\\$&'),
    'im'
  );

  return (text: string) => reg.test(text);
};

export function setLocalStatusCall(callId: string, localStatus: string): void {
  localStorage.setItem(callId, localStatus);
}

export function getLocalStatusCall(callId: string) {
  return localStorage.getItem(callId) || '';
}

export function estimateCallAction(
  callId: string,
  callStatus: string,
  from?: string
): string {
  const localStatus = getLocalStatusCall(callId);
  const isFree = localStatus === '' || localStatus === 'end';

  switch (callStatus) {
    case 'invite':
      return 'invite';
    case 'ringing':
      return [''].includes(localStatus);
    case 'start':
      if (['', 'invite', 'start'].includes(localStatus)) return 'start';

      if (localStatus === 'ringing') return 'end';

      break;
    case 'end':
      if (!isFree) {
        return 'end';
      }
  }

  return 'nothing';
}

export const conversionStatusStr2Num = (
  status: number | ChatUserStatus,
  invisible?: string | number
) => {
  if (invisible) return UserStatusType.Invisible;

  if (status === null || status === undefined) return UserStatusType.Offline;

  let statusTmp = UserStatusType.Offline;

  if (typeof status === 'string') {
    switch (status) {
      case 'offline':
        statusTmp = UserStatusType.Offline;
        break;
      case 'online':
        statusTmp = UserStatusType.Online;
        break;
      case 'away':
        statusTmp = UserStatusType.Away;
        break;
      case 'busy':
        statusTmp = UserStatusType.Busy;
        break;

      default:
        break;
    }
  } else {
    statusTmp = status;
  }

  return statusTmp;
};

export const conversionStatusNum2Str = (
  status: number,
  invisible?: string | number
): string | ChatUserStatus => {
  if (invisible) return 'invisible';

  if (status === null || status === undefined) return 'offline';

  let statusTmp = 'offline';

  if (typeof status === 'number') {
    switch (status) {
      case UserStatusType.Offline:
        statusTmp = 'offline';
        break;
      case UserStatusType.Online:
        statusTmp = 'online';
        break;
      case UserStatusType.Away:
        statusTmp = 'away';
        break;
      case UserStatusType.Busy:
        statusTmp = 'busy';
        break;

      default:
        break;
    }
  } else {
    statusTmp = status;
  }

  return statusTmp;
};

export const getReactionExist = (
  reactions: Record<string, any>,
  reactionList: any[]
) => {
  const checkReactionExist = (item: string) => {
    const idReaction = isString(item) ? item.split(':')[1].split('_')[1] : null;

    return reactionList.some(reaction => reaction.id == idReaction);
  };

  const reactionExists = reactions
    ? Object.keys(reactions).filter(checkReactionExist)
    : [];

  return reactionExists.reduce((acc, item) => {
    acc[item] = reactions[item];

    return acc;
  }, {} as Record<string, any>);
};
