/**
 * @type: embedView
 * name: forum_post.embedItem.insideFeedItem
 * chunkName: feed_embed
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/forum/hocs/connectForumPost';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
