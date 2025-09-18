import { HandleAction, useGlobal } from '@metafox/framework';
import { styled, SxProps } from '@mui/material';
import React from 'react';
import MsgActionMenu from './MsgActionMenu';
import MsgEmojiPicker from './MsgEmojiPicker';
import { IPropClickAction } from '@metafox/chatplus/types';

interface Props {
  disableReact: boolean;
  disabled: boolean;
  handleAction: HandleAction;
  items?: any;
  identity: string;
  isOwner?: boolean;
  showHover?: boolean;
  placement?: any;
  onClickAction?: (obj: IPropClickAction) => void;
  showActionRef?: any;
  sxFieldWrapper?: SxProps;
  [key: string]: any;
}

const name = 'MsgToolbar';

const UIChatToolbar = styled('div', {
  name,
  slot: 'uiChatToolbar',
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center',
  flex: 1,
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
  showHover,
  placement,
  onClickAction,
  showActionRef,
  sxFieldWrapper,
  rest
}: Props) {
  const { useScrollRef } = useGlobal();

  const scrollRef = useScrollRef();

  if (disabled) return null;

  return (
    <UIChatToolbar isOwner={isOwner} sx={sxFieldWrapper}>
      <MsgActionMenu
        identity={identity}
        items={items}
        scrollRef={scrollRef}
        handleAction={handleAction}
        showHover={showHover}
        placement={placement}
        popperOptions={{
          strategy: 'fixed'
        }}
        onClickAction={onClickAction}
        showActionRef={showActionRef}
        {...rest}
      />
      {!disableReact ? (
        <MsgEmojiPicker
          scrollRef={scrollRef}
          handleAction={handleAction}
          identity={identity}
          showHover={showHover}
          onClickAction={onClickAction}
          showActionRef={showActionRef}
        />
      ) : null}
    </UIChatToolbar>
  );
}
