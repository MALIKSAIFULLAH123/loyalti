/**
 * @type: itemView
 * name: marketplace_invoice.itemView.mainCard
 * chunkName: marketplace
 */

import {
  connectItemView,
  actionCreators
} from '@metafox/marketplace/hocs/connectInvoice';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
