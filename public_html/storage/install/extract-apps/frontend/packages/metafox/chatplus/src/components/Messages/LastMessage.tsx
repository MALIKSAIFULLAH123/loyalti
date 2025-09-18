import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import { camelCase, isEmpty } from 'lodash';
import {
  formatLastMsg,
  isCallMessage
} from '@metafox/chatplus/services/formatTextMsg';
import { useSessionUser } from '@metafox/chatplus/hooks';
import { checkIsQuote } from '../MsgContent/MessageText';

const ItemRowTitleWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  marginTop: theme.spacing(0.25)
}));

const UnReadDot = styled('span', { slot: 'UnReadDot' })(({ theme }) => ({
  display: 'inline-block',
  width: 12,
  height: 12,
  backgroundColor: theme.palette.primary.main,
  borderRadius: 20,
  marginTop: theme.spacing(0.25)
}));
const ItemMsgText = styled('div', {
  shouldForwardProp: props => props !== 'unread'
})<{ unread?: any }>(({ theme, unread }) => ({
  display: 'block',
  color: theme.palette.text.secondary,
  padding: 0,
  minWidth: 0,
  maxWidth: 'calc(100% - 12px)',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  fontSize: theme.spacing(1.625),
  lineHeight: theme.spacing(2.35),
  whiteSpace: 'nowrap',
  ...(unread && {
    fontWeight: theme.typography.fontWeightBold
  }),
  a: {
    color: theme.palette.text.secondary,
    pointerEvents: 'none'
  },
  br: {
    display: 'none'
  }
}));

interface LastMsgProps {
  room: any;
  unread: any;
  subscription: any;
}

export default function LastMessage({
  room,
  unread,
  subscription
}: LastMsgProps) {
  const { i18n } = useGlobal();
  const userSession = useSessionUser();

  const message = room?.lastMessage;

  if (!room || room?.no_join || isEmpty(message)) return null;

  let lastMessage: any = '';
  const msgType = message?.t || message?.type;
  let onlyMessage = false;

  const isOwnerToMe = (message?.owners || [])
    .map(item => item._id)
    .includes(userSession._id);
  const isUserToMe = userSession?._id === message?.u?._id ? 1 : 0;

  if (msgType) {
    let msg = message.msg;
    let key = `user_${msgType}${message?.role ? `_${message.role}` : ''}`;

    key = key.replace(/-/g, '_');

    if (msgType === 'room_changed_privacy') {
      msg = msg === 'public' ? 'Public' : 'Private';
    }

    switch (true) {
      case msgType === 'deleted' || msgType === 'rm':
        key = 'message_was_deleted';

        break;
      case isCallMessage(msgType): {
        if (/(invite)_/.test(msgType) || /(start)_/.test(msgType)) {
          key = 'started_a_call';

          if (!isUserToMe) {
            key = 'user_started_call';
          }
        } else if (/(miss)_/.test(msgType) && !isUserToMe) {
          key = 'missed_a_call';
        } else {
          key = 'call_ended';
        }

        break;
      }
      default:
        break;
    }

    lastMessage = key
      ? i18n.formatMessage(
          { id: key },
          {
            msg,
            type: msgType,
            name: message?.u?.name,
            role: message?.role,
            user: <b>{message?.u?.name}</b>,
            bold: (str: string) => <b>{str}</b>,
            owner: message?.owners?.length ? (
              <b>{message?.owners[0].name}</b>
            ) : (
              ''
            ),
            username: message?.u?.name,
            isUserToMe,
            isOwnerToMe: isOwnerToMe ? 1 : 0,
            remaining: (message?.total || 1) - 1
          }
        )
      : '';
  } else {
    if (message?.attachments?.length > 0) {
      if (message.attachments[0].type === 'file') {
        const countImageAttachment = message.attachments.filter(
          item => item.image_url
        ).length;

        if (message.attachments[0]?.image_url) {
          lastMessage = i18n.formatMessage(
            { id: 'user_sent_photos' },
            {
              isUserToMe: isUserToMe ? 1 : 0,
              total: countImageAttachment,
              username: message?.u?.name
            }
          );
        } else if (message.attachments[0]?.audio_url) {
          lastMessage = i18n.formatMessage(
            { id: 'user_sent_an_audio_clip' },
            {
              isUserToMe: isUserToMe ? 1 : 0,
              username: message?.u?.name
            }
          );
        } else if (message.attachments[0]?.video_url) {
          lastMessage = i18n.formatMessage(
            { id: 'user_sent_a_video' },
            {
              isUserToMe: isUserToMe ? 1 : 0,
              username: message?.u?.name
            }
          );
        } else {
          lastMessage = i18n.formatMessage(
            { id: 'user_sent_an_attachment' },
            {
              isUserToMe: isUserToMe ? 1 : 0,
              username: message?.u?.name
            }
          );
        }
      } else {
        const { isQuote } = checkIsQuote(message?.attachments);

        if (isQuote) {
          const msgFormat = formatLastMsg(message);

          lastMessage = msgFormat ? (
            <span dangerouslySetInnerHTML={{ __html: msgFormat }} />
          ) : (
            ''
          );
        }
      }
    } else {
      const msgFormat = formatLastMsg(message);

      lastMessage = msgFormat ? (
        <span dangerouslySetInnerHTML={{ __html: msgFormat }} />
      ) : (
        ''
      );
      onlyMessage = true;
    }
  }

  if (isEmpty(lastMessage)) return null;

  const userNameMessage = () => {
    let result = i18n.formatMessage(
      { id: 'user_sent_a_message' },
      {
        isUserToMe: isUserToMe ? 1 : 0,
        username: message?.u?.name,
        msg: onlyMessage ? lastMessage : ''
      }
    );

    if (msgType || message?.attachments?.length > 0) {
      result = '';
    }

    return result;
  };

  return (
    <ItemRowTitleWrapper data-testid={camelCase('last message')}>
      <ItemMsgText unread={unread}>
        {onlyMessage ? userNameMessage() : lastMessage}
      </ItemMsgText>
      {subscription?.alert || !!subscription?.unread ? <UnReadDot /> : null}
    </ItemRowTitleWrapper>
  );
}
