/**
 * @type: itemView
 * name: forum.itemView.sliderCard
 * chunkName: forum
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/forum/hocs/connectForum';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
