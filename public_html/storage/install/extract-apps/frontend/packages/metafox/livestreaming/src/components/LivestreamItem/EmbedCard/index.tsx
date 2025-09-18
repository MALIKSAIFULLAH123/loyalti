/**
 * @type: embedView
 * name: live_video.embedItem.insideFeedItem
 * chunkName: feed_embed
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/livestreaming/hocs/connectLivestreamItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
