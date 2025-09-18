/**
 * @type: ui
 * name: chatplus.messageContent.system
 * chunkName: chatplusUI
 */

import { useSessionUser } from '@metafox/chatplus/hooks';
import { MsgContentProps } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import MsgAttachments from '../MsgAttachments';
import MsgEmbeds from '../MsgEmbeds';

const TitleStyled = styled('div')(({ theme }) => ({
  fontSize: theme.spacing(1.75),
  color:
    theme.palette.mode === 'dark'
      ? theme.palette.text.primary
      : theme.palette.grey['700']
}));

const UIChatMsgItemBodyInnerWrapper = styled('div')(({ theme }) => ({
  margin: theme.spacing(1, 0)
}));

export default function MsgContentSystem({
  message,
  msgType
}: MsgContentProps) {
  const { i18n } = useGlobal();
  const userSession = useSessionUser();

  let msg = message.msg;

  if (message.t === 'room_changed_privacy') {
    msg = msg === 'public' ? 'Public' : 'Private';
  }

  const isOwnerToMe = (message?.owners || [])
    .map(item => item._id)
    .includes(userSession._id);

  const title = i18n.formatMessage(
    { id: `user_${msgType}${message.role ? `_${message.role}` : ''}` },
    {
      msg: <b>{msg}</b>,
      type: message.type,
      name: message.u.name,
      role: message.role,
      user: <b>{message.u.name}</b>,
      owner: message?.owners?.length ? <b>{message?.owners[0].name}</b> : '',
      isUserToMe: userSession._id === message.u._id ? 1 : 0,
      isOwnerToMe: isOwnerToMe ? 1 : 0,
      remaining: (message?.total || 1) - 1,
      bold: (str: string) => <b>{str}</b>
    }
  );

  return (
    <UIChatMsgItemBodyInnerWrapper>
      <TitleStyled>{title}</TitleStyled>
      <MsgAttachments message={message} />
      <MsgEmbeds message={message} />
    </UIChatMsgItemBodyInnerWrapper>
  );
}
