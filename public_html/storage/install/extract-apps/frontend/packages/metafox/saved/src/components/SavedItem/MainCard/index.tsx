/**
 * @type: itemView
 * name: saved.itemView.mainCard
 * title: Main Card
 * chunkName: saved
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectSavedItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
