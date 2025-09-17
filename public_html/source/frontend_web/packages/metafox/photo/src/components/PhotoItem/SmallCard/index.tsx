/**
 * @type: itemView
 * name: photo.itemView.smallCard
 * chunkName: photo
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/photo/hocs/connectPhoto';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
