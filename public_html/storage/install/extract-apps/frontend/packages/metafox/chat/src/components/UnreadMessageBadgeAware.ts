import { useGlobal } from '@metafox/framework';
import { useGetNotifications } from '../hooks';

export default function UnreadMessageBadgeAware() {
  const { dispatch } = useGlobal();
  const notifications = useGetNotifications();

  const badgeTotalUnread =
    notifications?.total_notification > 99
      ? '99+'
      : notifications?.total_notification;

  dispatch({
    type: 'core/status/fulfill',
    payload: { chat_message: badgeTotalUnread }
  });

  return null;
}
