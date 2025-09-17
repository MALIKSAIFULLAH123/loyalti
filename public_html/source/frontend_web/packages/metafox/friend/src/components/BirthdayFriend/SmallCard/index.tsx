/**
 * @type: itemView
 * name: friend.itemView.birthdaySmallCard
 * chunkName: friend
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectFriendItemView';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
