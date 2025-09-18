/**
 * @type: itemView
 * name: forum_thread.itemView.mainCard
 * chunkName: forum
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/forum/hocs/connectForumThread';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
