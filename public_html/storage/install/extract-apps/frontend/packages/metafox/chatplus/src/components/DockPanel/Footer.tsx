import { ReactMode } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const name = 'ChatDockFooter';

const Root = styled('div', {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'isAllPageMessages'
})<{ isAllPageMessages?: boolean }>(({ theme, isAllPageMessages }) => ({
  position: 'relative',
  display: 'flex',
  minHeight: theme.spacing(5),
  zIndex: theme.zIndex.tooltip + 11,
  backgroundColor: theme.palette.background.paper,
  borderTop: theme.mixins.border('divider'),
  ...(isAllPageMessages && {
    minHeight: theme.spacing(6)
  })
}));

const UIChatRoomFooter = styled('div', {
  name,
  slot: 'uiChatRoomFooter',
  shouldForwardProp: props =>
    props !== 'disable' && props !== 'isAllPageMessages'
})<{ disable?: boolean; isAllPageMessages?: boolean }>(
  ({ theme, disable, isAllPageMessages }) => ({
    position: 'relative',
    borderTop: theme.mixins.border('secondary'),
    display: 'flex',
    alignItems: 'flex-end',
    minHeight: theme.spacing(5),
    zIndex: theme.zIndex.tooltip + 11,
    flexFlow: 'wrap',
    justifyContent: 'center',
    ...(disable && {
      userSelect: 'none',
      backgroundColor:
        theme.palette.mode === 'dark'
          ? theme.palette.grey['700']
          : theme.palette.background.paper
    }),
    ...(isAllPageMessages && {
      minHeight: theme.spacing(6)
    })
  })
);
const UIChatComposerContainer = styled('div', {
  name,
  slot: 'uiChatComposerContainer'
})(({ theme }) => ({
  textAlign: 'center',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  height: '100%',
  padding: '4px 8px',
  color: theme.palette.grey['500']
}));

interface Props {
  children: JSX.Element;
  archived?: boolean;
  isReadOnly?: boolean;
  isMuted?: boolean;
  searching?: boolean;
  isBlocked?: boolean;
  reactMode?: ReactMode;
  allowMsgNoOne?: boolean;
}

export default function ChatDockFooter({
  children,
  archived,
  isReadOnly,
  isMuted,
  searching,
  isBlocked,
  reactMode,
  allowMsgNoOne
}: Props) {
  const { i18n, usePageParams } = useGlobal();
  const pageParams = usePageParams();

  const isAllPageMessages = pageParams?.isAllPageMessages || false;

  if (searching) return null;

  if (archived) {
    return (
      <UIChatRoomFooter disable isAllPageMessages={isAllPageMessages}>
        <UIChatComposerContainer>
          {i18n.formatMessage({ id: 'this_group_is_archived' })}
        </UIChatComposerContainer>
      </UIChatRoomFooter>
    );
  }

  if (isReadOnly) {
    return (
      <UIChatRoomFooter disable isAllPageMessages={isAllPageMessages}>
        <UIChatComposerContainer>
          {i18n.formatMessage({ id: 'this_group_is_readonly' })}
        </UIChatComposerContainer>
      </UIChatRoomFooter>
    );
  }

  if (isMuted) {
    return (
      <UIChatRoomFooter disable isAllPageMessages={isAllPageMessages}>
        <UIChatComposerContainer>
          {i18n.formatMessage({ id: 'you_have_been_muted_in_this_group' })}
        </UIChatComposerContainer>
      </UIChatRoomFooter>
    );
  }

  if (isBlocked || (allowMsgNoOne && reactMode !== 'edit')) {
    return (
      <UIChatRoomFooter disable isAllPageMessages={isAllPageMessages}>
        <UIChatComposerContainer>
          {i18n.formatMessage({
            id: 'you_can_not_send_message_in_this_conversation'
          })}
        </UIChatComposerContainer>
      </UIChatRoomFooter>
    );
  }

  return <Root isAllPageMessages={isAllPageMessages}>{children}</Root>;
}
