/**
 * @type: ui
 * name: chatplus.messageContent.groupCall
 * chunkName: chatplusUI
 */

import { MsgContentProps } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import MsgActions, { MsgAction } from '../MsgActions';
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

const UIChatMsgCallActionLink = styled(MsgAction, {
  name,
  slot: 'uiChatMsgCallActionLink'
})(({ theme }) => ({
  height: theme.spacing(1.5),
  fontSize: theme.spacing(1.625)
}));

export default function GroupCallEnded({
  message,
  user,
  createdDate,
  isRoomLimited,
  msgType
}: MsgContentProps) {
  const { chatplus, i18n } = useGlobal();

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
        {!isRoomLimited ? (
          <MsgActions>
            <UIChatMsgCallActionLink
              onClick={() => chatplus.joinCallFromMessage(message)}
              label={i18n.formatMessage({ id: 'join' })}
            />
          </MsgActions>
        ) : null}
        <CallReports reports={message.reports} user={user} />
      </UIChatMsgItemCall>
    </div>
  );
}
