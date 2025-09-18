/**
 * @type: itemView
 * name: marketplace.itemView.smallCard
 * chunkName: marketplace
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/marketplace/hocs/connectMarketplaceItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
