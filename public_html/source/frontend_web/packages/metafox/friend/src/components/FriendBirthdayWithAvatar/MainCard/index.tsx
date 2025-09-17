/**
 * @type: itemView
 * name: friend.itemView.birthdayWidthAvatar
 * chunkName: friend
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectFriendItemView';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
