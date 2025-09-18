/**
 * @type: ui
 * name: chat.buttonAddChatRoom
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Tooltip } from '@mui/material';
import React from 'react';

const WrapperButtonIcon = styled(Button)(({ theme }) => ({
  padding: theme.spacing(0, 1),
  height: 'auto',
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.primary,
  fontSize: theme.spacing(2.75),
  minWidth: theme.spacing(6.25),
  '& .ico-circle ': {
    fontSize: theme.spacing(1.75)
  },
  '& .ico-gear-o': {
    fontSize: theme.spacing(1.75)
  },
  '& .ico-inbox-o': {
    fontSize: theme.spacing(1.75)
  }
}));

const AddChatRoom = () => {
  const { dispatch, i18n } = useGlobal();

  const handleAddConversation = () => {
    dispatch({
      type: 'chat/chatroom/addConversation',
      payload: {}
    });
  };

  return (
    <Tooltip
      title={i18n.formatMessage({ id: 'new_conversation' }) ?? ''}
      placement="top"
    >
      <WrapperButtonIcon onClick={handleAddConversation}>
        <LineIcon icon="ico-compose" />
      </WrapperButtonIcon>
    </Tooltip>
  );
};

export default AddChatRoom;
