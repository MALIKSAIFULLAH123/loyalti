import { HandleAction, useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import MsgActionMenu from './MsgActionMenu';
import MsgEmojiPicker from './MsgEmojiPicker';

interface Props {
  disableReact: boolean;
  disabled: boolean;
  handleAction: HandleAction;
  items?: any[];
  identity: string;
  isOwner?: boolean;
  [key: string]: any;
}

const name = 'MsgToolbar';

const UIChatToolbar = styled('div', {
  name,
  slot: 'uiChatToolbar',
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  ...(!isOwner && {
    flexDirection: 'row-reverse'
  })
}));

export default function MsgToolbar({
  items,
  disabled,
  disableReact,
  handleAction,
  identity,
  isOwner,
  ...rest
}: Props) {
  const { useScrollRef } = useGlobal();

  const scrollRef = useScrollRef();

  if (disabled) return null;

  return (
    <UIChatToolbar isOwner={isOwner} className={'uiChatToolbar'}>
      <MsgActionMenu
        identity={identity}
        items={items}
        scrollRef={scrollRef}
        handleAction={handleAction}
        {...rest}
      />
      {!disableReact ? (
        <MsgEmojiPicker
          scrollRef={scrollRef}
          handleAction={handleAction}
          identity={identity}
        />
      ) : null}
    </UIChatToolbar>
  );
}
