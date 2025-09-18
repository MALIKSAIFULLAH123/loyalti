import { HandleAction } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Tooltip } from '@mui/material';
import React from 'react';
import AttachEmojiButton from './AttachEmojiButton';
import { IPropClickAction } from '@metafox/chatplus/types';

const name = 'MsgEmojiPicker';

const UIChatItemBtn = styled(Button, {
  name,
  slot: 'UIChatItemBtn',
  shouldForwardProp: props => props !== 'showHover'
})<{ showHover?: boolean }>(({ theme, showHover }) => ({
  position: 'relative',
  visibility: showHover ? 'hidden' : 'unset',
  padding: theme.spacing(1, 0.75),
  cursor: 'pointer',
  minWidth: theme.spacing(2.5),
  lineHeight: theme.spacing(2.5),
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.secondary
}));

const Control = React.forwardRef(({ title, ...rest }: any, ref: any) => {
  return (
    <Tooltip title={title} placement="top">
      <UIChatItemBtn
        className={'uiChatItemBtn uiChatIconBtn'}
        disableFocusRipple
        disableRipple
        disableTouchRipple
        {...rest}
        ref={ref}
      >
        <LineIcon icon="ico-smile-o" />
      </UIChatItemBtn>
    </Tooltip>
  );
});

interface Props {
  handleAction: HandleAction;
  scrollRef: React.MutableRefObject<HTMLDivElement>;
  identity: string;
  showHover?: boolean;
  onClickAction?: (obj: IPropClickAction) => void;
  showActionRef?: any;
}

export default function MsgEmojiPicker({
  handleAction,
  scrollRef,
  identity,
  showHover = true,
  ...rest
}: Props) {
  const handleEmojiClick = React.useCallback(
    (shortcut: string, unicode: string = null) =>
      handleAction('chatplus/messageReaction', { shortcut, unicode }),
    [handleAction]
  );

  const unsetReaction = React.useCallback(
    (shortcut: string) => {
      handleAction('chatplus/unsetReaction', { identity, shortcut });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [handleAction]
  );

  return (
    <AttachEmojiButton
      size="small"
      multiple={false}
      onEmojiClick={handleEmojiClick}
      unsetReaction={unsetReaction}
      scrollRef={scrollRef}
      scrollClose
      identity={identity}
      control={Control}
      showHover={showHover}
      {...rest}
    />
  );
}
