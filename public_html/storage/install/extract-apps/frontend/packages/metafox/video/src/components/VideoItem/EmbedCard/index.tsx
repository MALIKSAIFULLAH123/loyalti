/**
 * @type: embedView
 * name: video.embedItem.insideFeedItem
 * chunkName: feed_embed
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/video/hocs/connectVideoItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
