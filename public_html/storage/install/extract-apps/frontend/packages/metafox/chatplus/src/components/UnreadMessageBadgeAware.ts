import { useGlobal } from '@metafox/framework';
import React from 'react';
import { useGetNotifications } from '../hooks';

export default function UnreadMessageBadgeAware() {
  const { dispatch } = useGlobal();
  const notifications = useGetNotifications();

  const badgeTotalUnread =
    notifications?.unread > 99 ? '99+' : notifications?.unread;

  React.useEffect(() => {
    dispatch({
      type: 'core/status/fulfill',
      payload: { new_chat_message: badgeTotalUnread }
    });
  });

  return null;
}
