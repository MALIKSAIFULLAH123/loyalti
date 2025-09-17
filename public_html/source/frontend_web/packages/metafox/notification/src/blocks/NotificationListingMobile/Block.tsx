/**
 * @type: block
 * name: notification.block.notificationListingBlockMobile
 * title: Notifications
 * keywords: notification
 * description: Display notification items of current logged users.
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

const NotificationListingBlock = createBlock<ListViewBlockProps>({
  name: 'NotificationListingBlock',
  extendBlock: Base,
  defaults: {
    canLoadMore: true,
    blockLayout: 'Large Main Lists Mobile'
  }
});

export default NotificationListingBlock;
