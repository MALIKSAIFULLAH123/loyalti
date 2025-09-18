/**
 * @type: ui
 * name: chat.messageContent.messageDeleted
 */

import { convertDateTime } from '@metafox/chat/utils';
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

export default function MessageDeleted({ message, isOwner }) {
  const { i18n, useGetItem } = useGlobal();

  const user = useGetItem(message?.user);
  const deleteDateTime = convertDateTime(message?.updated_at);
  const createdDateTime = convertDateTime(message?.created_at);

  let title = i18n.formatMessage({ id: 'you_deleted_a_message' });

  if (!isOwner) {
    if (user) {
      title = i18n.formatMessage(
        { id: 'user_message_is_removed' },
        {
          user: user.full_name || user.user_name
        }
      );
    } else {
      title = i18n.formatMessage({ id: 'message_was_deleted' });
    }
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
