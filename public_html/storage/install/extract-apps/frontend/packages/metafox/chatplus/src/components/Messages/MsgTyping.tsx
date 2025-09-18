import { styled } from '@mui/material';
import { keyframes } from '@emotion/react';
import { isEmpty, isFunction } from 'lodash';
import React from 'react';
import Avatar from '../Avatar';

const name = 'MsgTyping';
const UIChatMsgSet = styled('div', {
  name,
  slot: 'uiChatMsgSet',
  shouldForwardProp: prop => prop !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  display: 'flex',
  flexDirection: 'row',
  padding: theme.spacing(0.5, 2),
  ...(isOwner && { flexDirection: 'row-reverse' })
}));

const UIChatMsgSetBody = styled('div', { name, slot: 'uiChatMsgSetBody' })(
  ({ theme }) => ({
    backgroundColor: theme.palette.mode === 'light' ? '#f8f8f8' : '#828080',
    borderRadius: theme.spacing(2),
    fontSize: theme.mixins.pxToRem(12),
    display: 'inline-block'
  })
);
const chatplusTypingDot1 = keyframes`
    0% {transform: translateY(0);}
    25%{transform: translateY(-5px);}
    55% {transform: translateY(0);}
    100% {transform: translateY(0);}
`;
const chatplusTypingDot2 = keyframes`
    0% {transform: translateY(0);}
    20%{transform: translateY(0);}
    50% {transform: translateY(-5px);}
    80% {transform: translateY(0);}
    100% {transform: translateY(0);}
`;
const chatplusTypingDot3 = keyframes`
  0% {transform: translateY(0);}
  40%{transform: translateY(0);}
  70% {transform: translateY(-5px);}
  100% {transform: translateY(0);}
`;

const UIChatMsgTypingIcon = styled('div', {
  name,
  slot: 'uiChatMsgTypingIcon'
})(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'flex-end',
  justifyContent: 'center',
  '& > span': {
    display: 'inline-flex',
    width: '6px',
    height: '6px',
    borderRadius: '100%',
    background: theme.palette.mode === 'light' ? '#828080' : '#cecece',
    margin: '0 2px',

    '&:nth-of-type(1)': {
      animation: `${chatplusTypingDot1} 1.3s infinite`
    },
    '&:nth-of-type(2)': {
      animation: `${chatplusTypingDot2} 1.3s infinite`
    },
    '&:nth-of-type(3)': {
      animation: `${chatplusTypingDot3} 1.3s infinite`
    }
  }
}));

const UIChatMsgItemMsg = styled('div', {
  name,
  slot: 'uiChatMsgItemMsg'
})(({ theme }) => ({
  borderRadius: theme.spacing(1),
  fontSize: theme.spacing(1.75),
  padding: theme.spacing(1),
  display: 'inline-block'
}));

const UIChatMsgSetAvatar = styled('div', {
  name,
  slot: 'uiChatMsgSetAvatar'
})(({ theme }) => ({
  marginRight: theme.spacing(1)
}));

interface Props {
  onInit?: () => {};
  typings: any[];
}

export default function MsgTyping({ onInit, typings }: Props) {
  React.useEffect(() => {
    if (isFunction(onInit)) {
      onInit();
    }
  }, [onInit]);

  if (isEmpty(typings)) return null;

  return (
    <>
      {typings.slice(0, 2).map((item, idx) => (
        <UIChatMsgSet key={idx}>
          <UIChatMsgSetAvatar>
            <Avatar
              name={item?.name || item?.username}
              username={item?.username}
              size={32}
              avatarETag={item?.avatarETag}
            />
          </UIChatMsgSetAvatar>
          <UIChatMsgSetBody>
            <UIChatMsgItemMsg>
              <UIChatMsgTypingIcon>
                <span></span>
                <span></span>
                <span></span>
              </UIChatMsgTypingIcon>
            </UIChatMsgItemMsg>
          </UIChatMsgSetBody>
        </UIChatMsgSet>
      ))}
    </>
  );
}
