/**
 * @type: itemView
 * name: search.itemView.mainCard
 * chunkName: search
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/search/hocs/connectSearchItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
