/**
 * @type: itemView
 * name: notification.itemView.smallCard
 * chunkName: notification
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/notification/hocs/connectNotificationItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
