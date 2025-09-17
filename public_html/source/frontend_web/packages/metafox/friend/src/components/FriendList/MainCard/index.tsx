/**
 * @type: itemView
 * name: friend_list.itemView.mainCard
 * chunkName: friend
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectFriendListItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
