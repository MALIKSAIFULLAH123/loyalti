import { MsgItemShape, RoomItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, styled, Tooltip } from '@mui/material';
import React from 'react';

const name = 'ChatPlus-ChatBotAction';

const RootStyled = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  gap: theme.spacing(0.75),
  marginTop: theme.spacing(0.25)
}));

const IconBtn = styled('div', {
  name,
  slot: 'removeBtn',
  shouldForwardProp: props => props !== 'isReport'
})<{ isReport?: boolean }>(({ theme, isReport }) => ({
  width: theme.spacing(3.75),
  height: theme.spacing(3.75),
  color: theme.palette.text.primary,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer',
  ...(isReport && {
    transform: 'rotateY(180deg)'
  }),
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16)
  },
  '&:hover': {
    borderRadius: theme.shape.borderRadius,
    backgroundColor: theme.palette.grey[100]
  }
}));

interface Props {
  isOwner?: boolean;
  message?: MsgItemShape | undefined;
  room?: RoomItemShape;
}

function ChatBotAction({ message, room }: Props) {
  const { i18n, dispatch } = useGlobal();

  const handleLike = () => {
    dispatch({
      type: 'chatplus/chatbot/likeItem',
      payload: { room_id: room?.id, message_id: message?.id }
    });
  };

  const handleReport = () => {
    dispatch({
      type: 'chatplus/chatbot/reportItem',
      payload: { room_id: room?.id, message_id: message?.id }
    });
  };

  return (
    <RootStyled>
      <Tooltip title={i18n.formatMessage({ id: 'like' })}>
        <IconBtn onClick={handleLike}>
          <LineIcon icon="ico-thumbup-o" />
        </IconBtn>
      </Tooltip>
      <Tooltip title={i18n.formatMessage({ id: 'chatgpt_bot_report' })}>
        <IconBtn isReport onClick={handleReport}>
          <LineIcon icon="ico-thumbdown-o" />
        </IconBtn>
      </Tooltip>
    </RootStyled>
  );
}

export default ChatBotAction;
