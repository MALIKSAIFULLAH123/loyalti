/**
 * @type: itemView
 * name: subscription_invoice.itemView.mainCard
 * chunkName: subscription_invoice
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/subscription/hocs/connectInvoice';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
