/**
 * @type: itemView
 * name: announcement.itemView.mainCard
 * chunkName: boot_homepage
 */

import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectAnnouncementItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators, { categories: true });
