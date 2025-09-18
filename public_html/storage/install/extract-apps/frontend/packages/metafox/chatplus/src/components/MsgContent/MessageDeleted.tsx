/**
 * @type: ui
 * name: chatplus.messageContent.messageDeleted
 * chunkName: chatplusUI
 */

import { convertDateTime } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { styled, Tooltip } from '@mui/material';
import React from 'react';

const UIChatMsgItemBodyInnerWrapper = styled('div')(({ theme }) => ({
  borderRadius: theme.spacing(0.5),
  padding: theme.spacing(1.25),
  border: theme.mixins.border('secondary'),
  backgroundColor: theme.palette.background.paper,
  color: theme.palette.text.primary,
  fontStyle: 'italic'
}));

export default function MessageDeleted({ message, user }) {
  const { i18n } = useGlobal();
  const deleteDateTime = convertDateTime(message?.editedAt?.$date);
  const createdDateTime = convertDateTime(message?.ts?.$date);

  let title = i18n.formatMessage(
    { id: 'user_message_is_removed' },
    {
      user: message.editedBy.name || message.u.name
    }
  );

  if (message?.editedBy?.username === user?.username) {
    title = i18n.formatMessage({ id: 'you_deleted_a_message' });
  }

  const Time = () => (
    <>
      {createdDateTime ? (
        <div>
          {i18n.formatMessage({ id: 'sent' })}: {createdDateTime}
        </div>
      ) : null}
      {deleteDateTime ? (
        <div>
          {i18n.formatMessage({ id: 'removed' })}: {deleteDateTime}
        </div>
      ) : null}
    </>
  );

  return (
    <Tooltip
      title={<Time />}
      placement="top"
      PopperProps={{
        disablePortal: true
      }}
    >
      <UIChatMsgItemBodyInnerWrapper className="uiChatMsgItemBodyInnerWrapper">
        {title}
      </UIChatMsgItemBodyInnerWrapper>
    </Tooltip>
  );
}
