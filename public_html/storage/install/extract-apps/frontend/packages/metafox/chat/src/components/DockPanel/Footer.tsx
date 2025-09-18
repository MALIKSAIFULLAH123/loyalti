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
  zIndex: 2,
  backgroundColor: theme.palette.background.paper,
  borderTop: theme.mixins.border('secondary'),
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
    zIndex: 2,
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
  searching?: boolean;
  isBlocked?: boolean;
  allowCompose?: boolean;
}

export default function ChatDockFooter({
  children,
  searching,
  isBlocked,
  allowCompose
}: Props) {
  const { i18n, usePageParams } = useGlobal();
  const pageParams = usePageParams();

  const isAllPageMessages = pageParams?.isAllPageMessages || false;

  if (searching) return null;

  if (isBlocked || !allowCompose) {
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
