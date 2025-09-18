/**
 * @type: itemView
 * name: subscription_invoice.itemView.smallCard
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/subscription/hocs/connectInvoice';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);