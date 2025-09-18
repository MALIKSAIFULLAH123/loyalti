/**
 * @type: itemView
 * name: saved.itemView.friendList
 * title: Friend Card
 * chunkName: saved
 */
import { actionCreators, connectItemView } from '../../hocs/connectSavedItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
