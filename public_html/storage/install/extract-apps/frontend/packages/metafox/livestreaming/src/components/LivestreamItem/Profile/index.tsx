/**
 * @type: itemView
 * name: live_video.itemView.profileCard
 * chunkName: livestreaming
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/livestreaming/hocs/connectLivestreamItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
