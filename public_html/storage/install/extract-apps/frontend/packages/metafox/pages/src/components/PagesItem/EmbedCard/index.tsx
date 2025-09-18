/**
 * @type: embedView
 * name: page.embedItem.insideFeedItem
 * chunkName: feed_embed
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/pages/hocs/connectPageItem';
import EmbedPageItem from './ItemView';

export default connectItemView(EmbedPageItem, actionCreators);
