/**
 * @type: itemView
 * name: group.itemView.mainCard
 * chunkName: group
 */
import { connect, GlobalState } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectGroupItem';
import ItemView from './ItemView';

const Enhancer = connect((state: GlobalState) => ({
  itemActionMenu: state._resourceMenus.group.group.itemActionMenu.items
}))(ItemView);

export default connectItemView(Enhancer, actionCreators);
