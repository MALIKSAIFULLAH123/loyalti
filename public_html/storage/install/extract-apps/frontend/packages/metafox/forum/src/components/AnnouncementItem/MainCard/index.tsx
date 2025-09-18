/**
 * @type: itemView
 * name: forum_announcement.itemView.mainCard
 * chunkName: forum_announcement
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/forum/hocs/connectForumThread';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
