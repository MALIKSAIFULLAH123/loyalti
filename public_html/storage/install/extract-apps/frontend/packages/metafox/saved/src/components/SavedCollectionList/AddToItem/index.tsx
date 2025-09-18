/**
 * @type: itemView
 * name: saved_collection_list.itemView.addToItem
 * title: Add To Item
 * chunkName: saved
 */

import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectSavedItemCollection';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
