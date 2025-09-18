/**
 * @type: itemView
 * name: video.itemView.smallCard
 * chunkName: video
 */

import {
  actionCreators,
  connectItemView
} from '@metafox/video/hocs/connectVideoItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
