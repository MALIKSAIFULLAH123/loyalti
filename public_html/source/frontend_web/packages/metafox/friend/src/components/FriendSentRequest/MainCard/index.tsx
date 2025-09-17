/**
 * @type: itemView
 * name: friend_sentRequest.itemView.mainCard
 * chunkName: friend
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectFriendRequestItemView';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
