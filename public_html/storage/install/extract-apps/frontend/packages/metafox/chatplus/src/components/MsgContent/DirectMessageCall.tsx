/**
 * @type: ui
 * name: chatplus.messageContent.directChatCallEnded
 * chunkName: chatplusUI
 */

import { MsgContentProps } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import MsgActions, { MsgAction } from '../MsgActions';

const name = 'DirectMessageCall';

const UIChatMsgItemCall = styled('div', {
  name,
  slot: 'uiChatMsgItemCall',
  shouldForwardProp: props => props !== 'missedCall'
})<{ missedCall?: boolean }>(({ theme, missedCall }) => ({
  padding: theme.spacing(1.5),
  paddingTop: theme.spacing(1),
  border: theme.mixins.border('secondary'),
  borderRadius: theme.spacing(0.5)
}));
const UIChatMsgItemCallTitle = styled('div', {
  name,
  slot: 'uiChatMsgItemCallTitle',
  shouldForwardProp: props => props !== 'missedCall'
})<{ missedCall?: boolean }>(({ theme, missedCall }) => ({
  fontWeight: theme.typography.fontWeightBold,
  ...(missedCall && {
    color: '#ee5a2b'
  })
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

export default function DirectMessageCall({
  message,
  isRoomLimited,
  createdDate,
  msgType
}: MsgContentProps) {
  const { chatplus, i18n } = useGlobal();

  const missedCall = /(miss)_(audio|video)_call_d/.test(msgType);

  const title = i18n.formatMessage(
    { id: `${msgType}` },
    {
      msg: message.msg,
      user: <b>{message.u.name}</b>
    }
  );

  return (
    <div className="uiChatMsgItemBodyInnerWrapper">
      <UIChatMsgItemCall missedCall={missedCall}>
        <UIChatMsgItemCallTitle missedCall={missedCall}>
          {title}
        </UIChatMsgItemCallTitle>
        <UIChatMsgItemTime>{createdDate}</UIChatMsgItemTime>
        {!isRoomLimited ? (
          <MsgActions>
            <UIChatMsgCallActionLink
              onClick={() => chatplus.callAgainFromMessage(message)}
              label={i18n.formatMessage({ id: 'call_again' })}
            />
          </MsgActions>
        ) : null}
      </UIChatMsgItemCall>
    </div>
  );
}
