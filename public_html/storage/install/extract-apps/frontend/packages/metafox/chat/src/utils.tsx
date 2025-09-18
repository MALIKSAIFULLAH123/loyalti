import moment from 'moment';
import { APP_CHAT, NOT_SEEN_MESSAGE_TYPES } from './constants';
import { MsgItemShape } from './types';
import { isString } from 'lodash';

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

export const countAttachmentImages = (attachments: any) => {
  return attachments?.length
    ? attachments.filter(item => item.image_url).length
    : 0;
};

export const isNotSeenMsg = (t: string) => NOT_SEEN_MESSAGE_TYPES.includes(t);

export const convertTypeMessage = ({
  msg,
  messagesDelete = []
}: {
  msg: MsgItemShape;
  messagesDelete?: any[];
}) => {
  let type = msg?.type;

  if (type === 'delete' || messagesDelete.includes(msg?.id)) {
    type = 'messageDeleted';
  }

  return type;
};

export const normalizeMsgItem = (msg: MsgItemShape) => {
  msg.id = msg.message_id || msg.id;
  msg.module_name = APP_CHAT;
  msg.resource_name = 'message';

  if (!msg.reactions) msg.reactions = undefined;

  msg.type = convertTypeMessage({ msg });

  msg.user_id = msg.user.id || msg.user_id;
};

export const filterImageAttachment = (attachments: any) => {
  const data =
    attachments && attachments.length
      ? attachments.filter((item: { is_image: any; image: any }) => {
          if (item.is_image) {
            return item.image;
          }
        })
      : [];
  const countImage = data.length > 0 ? data.length : 0;

  return {
    count: countImage,
    data
  };
};

export const triggerClick = (
  href: string,
  file_name?: any,
  targetBlank?: Boolean
) => {
  if (!href) {
    return null;
  }

  // let hrefParse = settings.siteUrl + '/' + href;

  const link = document.createElement('a', {});
  link.setAttribute('href', href);

  if (file_name) {
    link.setAttribute('download', file_name);
  }

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
