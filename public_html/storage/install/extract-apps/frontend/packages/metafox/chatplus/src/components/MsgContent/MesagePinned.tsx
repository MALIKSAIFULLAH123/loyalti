/**
 * @type: ui
 * name: chatplus.messageContent.messagePinned
 * chunkName: chatplusUI
 */

import { MsgContentProps } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import MsgAttachments from '../MsgAttachments';
import MsgEmbeds from '../MsgEmbeds';

const name = 'MesagePinned';

const UIChatMsgItemBodyInnerWrapper = styled('div', {
  name,
  slot: 'uiChatMsgItemBodyInnerWrapper',
  shouldForwardProp: prop => prop !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  width: '100%',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-start',
  ...(isOwner && { alignItems: 'flex-end' })
}));

const UIChatMsgItemPin = styled('div', {
  name,
  slot: 'uiChatMsgItemPin',
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  textAlign: 'start',
  ...(isOwner && {
    textAlign: 'end'
  })
}));

export default function MesagePinned({
  message,
  isOwner,
  msgType
}: MsgContentProps) {
  const { i18n } = useGlobal();

  const text =
    msgType === 'message_pinned'
      ? 'user_message_pinned'
      : 'user_message_unpinned';
  const title = i18n.formatMessage(
    { id: text },
    {
      msg: message.msg,
      user: <b>{message.u.name}</b>
    }
  );

  return (
    <UIChatMsgItemBodyInnerWrapper isOwner={isOwner}>
      <MsgAttachments message={message} msgType={msgType} isOwner={isOwner} />
      <MsgEmbeds message={message} />
      <UIChatMsgItemPin isOwner={isOwner}>{title}</UIChatMsgItemPin>
    </UIChatMsgItemBodyInnerWrapper>
  );
}
