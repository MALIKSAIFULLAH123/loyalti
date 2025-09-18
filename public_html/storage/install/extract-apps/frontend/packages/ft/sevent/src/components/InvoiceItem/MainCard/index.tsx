/**
 * @type: itemView
 * name: sevent_invoice.itemView.mainCard
 */

import {
  connectItemView,
  actionCreators
} from '@ft/sevent/hocs/connectInvoice';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
