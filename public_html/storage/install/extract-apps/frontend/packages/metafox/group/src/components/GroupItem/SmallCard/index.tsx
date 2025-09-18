/**
 * @type: itemView
 * name: groups.itemView.smallCard
 * chunkName: group
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectGroupItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
