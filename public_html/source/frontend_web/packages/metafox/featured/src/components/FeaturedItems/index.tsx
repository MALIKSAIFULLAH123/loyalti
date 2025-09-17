/**
 * @type: itemView
 * name: featured.itemView.item
 * chunkName: featured
 */

import { connectItemView } from '@metafox/framework';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
