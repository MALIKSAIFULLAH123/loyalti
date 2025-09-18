/**
 * @type: ui
 * name: chatplus.messageContent.groupCallEnded
 * chunkName: chatplusUI
 */

import { MsgContentProps } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import CallReports from './MsgCallReports';

const name = 'GroupCallEnded';
const UIChatMsgItemCall = styled('div', {
  name,
  slot: 'uiChatMsgItemCall',
  shouldForwardProp: props => props !== 'missedCall'
})<{ missedCall?: boolean }>(({ theme, missedCall }) => ({
  padding: theme.spacing(1, 1.5),
  border: theme.mixins.border('secondary'),
  borderRadius: theme.spacing(0.5)
}));

const UIChatMsgItemCallTitle = styled('div', {
  name,
  slot: 'uiChatMsgItemCallTitle'
})(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold
}));
const UIChatMsgItemTime = styled('div', {
  name,
  slot: 'uiChatMsgItemTime'
})(({ theme }) => ({
  color: theme.palette.grey['600'],
  marginTop: theme.spacing(0.5),
  textTransform: 'capitalize',
  fontSize: theme.spacing(1.5)
}));

export default function GroupCallEnded({
  message,
  user,
  createdDate,
  msgType
}: MsgContentProps) {
  const { i18n } = useGlobal();

  const title = i18n.formatMessage(
    { id: `${msgType}` },
    {
      msg: message.msg,
      user: <b>{message.u.name}</b>
    }
  );

  return (
    <div className="uiChatMsgItemBodyInnerWrapper">
      <UIChatMsgItemCall>
        <UIChatMsgItemCallTitle>{title}</UIChatMsgItemCallTitle>
        <UIChatMsgItemTime>{createdDate}</UIChatMsgItemTime>
        <CallReports reports={message.reports} user={user} />
      </UIChatMsgItemCall>
    </div>
  );
}
