/**
 * @type: itemView
 * name: gettingStarted.itemView.todoItem
 * chunkName: gettingStarted
 */
import { connectItemView } from '@metafox/framework';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
