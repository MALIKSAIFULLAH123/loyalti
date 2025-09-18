import { HandleAction } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Tooltip } from '@mui/material';
import React from 'react';
import AttachEmojiButton from './AttachEmojiButton';

const name = 'MsgEmojiPicker';

const UIChatItemBtn = styled(Button, {
  name,
  slot: 'UIChatItemBtn'
})(({ theme }) => ({
  position: 'relative',
  visibility: 'hidden',
  padding: theme.spacing(1, 0.75),
  cursor: 'pointer',
  minWidth: theme.spacing(2.5),
  lineHeight: theme.spacing(2.5)
}));

const Control = React.forwardRef(({ title, ...rest }: any, ref: any) => {
  return (
    <Tooltip
      title={title}
      placement="top"
      PopperProps={{
        disablePortal: true
      }}
    >
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
}

export default function MsgEmojiPicker({
  handleAction,
  scrollRef,
  identity
}: Props) {
  const handleEmojiClick = React.useCallback(
    (shortcut: string, unicode: string = null) =>
      handleAction('chat/messageReaction', { shortcut, unicode }),
    [handleAction]
  );

  const unsetReaction = React.useCallback(
    (shortcut: string) => {
      handleAction('chat/unsetReaction', { identity, shortcut });
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
      control={Control}
      scrollRef={scrollRef}
      scrollClose
      identity={identity}
    />
  );
}
