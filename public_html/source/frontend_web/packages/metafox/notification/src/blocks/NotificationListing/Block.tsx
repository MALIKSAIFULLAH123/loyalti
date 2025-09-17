/**
 * @type: block
 * name: notification.block.notificationListingBlock
 * title: Notifications
 * keywords: notification
 * description: Display notification items of current logged users.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import { NOTIFICATION_PAGING_IDS } from '@metafox/notification/constant';

const NotificationListingBlock = createBlock<ListViewBlockProps>({
  name: 'NotificationListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    title: 'Notifications',
    itemView: 'notification.itemView.mainCard',
    contentType: 'notification',
    dataSource: { apiUrl: '/notification' }
  },
  overrides: {
    pagingId: NOTIFICATION_PAGING_IDS,
    clearDataOnUnMount: true
  }
});

export default NotificationListingBlock;
