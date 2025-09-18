/**
 * @type: itemView
 * name: forum_post.itemView.mainCard
 * chunkName: forum
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/forum/hocs/connectForumPost';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
