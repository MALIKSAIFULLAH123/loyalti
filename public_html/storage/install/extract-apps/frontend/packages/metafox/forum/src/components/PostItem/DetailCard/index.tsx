/**
 * @type: itemView
 * name: forum_post.itemView.detailCard
 * chunkName: forum.detail
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/forum/hocs/connectForumThread';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
