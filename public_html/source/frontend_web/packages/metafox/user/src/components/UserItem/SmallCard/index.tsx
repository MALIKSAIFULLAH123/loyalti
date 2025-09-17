/**
 * @type: itemView
 * name: user.itemView.smallCard
 * chunkName: user
 */
import { actionCreators, connectItemView } from '../../../hocs/connectUserItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
