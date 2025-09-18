/**
 * @type: itemView
 * name: subscription_package.itemView.smallCard
 * chunkName: subscription_package
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/subscription/hocs/connectPackage';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
