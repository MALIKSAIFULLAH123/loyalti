import { styled } from '@mui/material';
import React from 'react';
import { MsgItemWrapperProps } from '../type';

const name = 'UIMsgItemBody';

const RootStyled = styled('div', {
  name,
  slot: 'RootStyled',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'isAlert' && prop !== 'isShowReact'
})<{ isOwner?: boolean; isAlert?: boolean; isShowReact?: boolean }>(
  ({ theme, isOwner, isAlert, isShowReact }) => ({
    display: 'flex',
    '@media (hover: hover)': {
      '&:hover .uiChatItemBtn': {
        visibility: 'visible'
      }
    },
    '@media (hover: none)': {
      '.uiChatItemBtn': {
        visibility: 'visible'
      }
    },
    [theme.breakpoints.down('sm')]: {
      '.uiChatItemBtn': {
        visibility: 'visible'
      }
    },
    ...(isOwner && {
      flexDirection: 'row-reverse'
    }),
    ...(isAlert && {
      textAlign: 'center',

      ...(isShowReact && {
        justifyContent: 'center'
      })
    })
  })
);

export default function UIMsgItemBody({
  children,
  isOwner,
  isAlert,
  isShowReact
}: MsgItemWrapperProps) {
  return (
    <RootStyled isOwner={isOwner} isAlert={isAlert} isShowReact={isShowReact}>
      {children}
    </RootStyled>
  );
}
