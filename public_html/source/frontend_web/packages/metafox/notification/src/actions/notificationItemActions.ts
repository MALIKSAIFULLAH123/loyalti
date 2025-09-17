import { HandleAction } from '@metafox/framework';

export default function notificationItemActions(dispatch: HandleAction) {
  return {
    markAsRead: () => dispatch('notification/markAsRead'),
    markAllRead: () => dispatch('notification/markAllRead'),
    deleteItem: () => dispatch('notification/deleteItem'),
    viewItem: () => dispatch('notification/viewItem')
  };
}
