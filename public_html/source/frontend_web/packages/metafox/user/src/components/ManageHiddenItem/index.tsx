/**
 * @type: itemView
 * name: user.itemView.hiddenUser
 * chunkName: feed
 */

import { connectItemView } from '@metafox/framework';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
