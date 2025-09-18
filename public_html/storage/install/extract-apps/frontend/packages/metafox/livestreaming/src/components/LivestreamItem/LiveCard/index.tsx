/**
 * @type: itemView
 * name: livestreaming.itemView.liveCard
 * chunkName: livestreaming.feed
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/livestreaming/hocs/connectLivestreamItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
