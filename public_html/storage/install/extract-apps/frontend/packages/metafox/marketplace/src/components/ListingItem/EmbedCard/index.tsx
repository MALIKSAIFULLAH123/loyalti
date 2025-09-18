/**
 * @type: embedView
 * name: marketplace.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/marketplace/hocs/connectMarketplaceItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
