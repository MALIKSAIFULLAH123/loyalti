/**
 * @type: itemView
 * name: blocked_user.itemView.smallCard
 * chunkName: user
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectBlockedUser';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
