/**
 * @type: itemView
 * name: featured.itemView.invoice
 * chunkName: featured
 */

import { connectItemView } from '@metafox/framework';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
