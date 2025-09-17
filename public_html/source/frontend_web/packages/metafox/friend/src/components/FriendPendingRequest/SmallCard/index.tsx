/**
 * @type: itemView
 * name: friend_pendingRequest.itemView.smallCard
 * chunkName: friend
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectFriendRequestItemView';
import FriendRequest from './ItemView';

export default connectItemView(FriendRequest, actionCreators);
