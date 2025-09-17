/**
 * @type: itemView
 * name: user.itemView.mainCard
 * chunkName: user
 */
import { connect, GlobalState } from '@metafox/framework';
import { actionCreators, connectItemView } from '../../../hocs/connectUserItem';
import ItemView from './ItemView';

const Enhancer = connect((state: GlobalState) => ({
  itemActionMenu: state._resourceMenus.user.user.itemActionMenu.items
}))(ItemView);

export default connectItemView(Enhancer, actionCreators);
