/**
 * @type: itemView
 * name: sevent.itemView.userCard
 */
import { connect, GlobalState } from '@metafox/framework';
import { actionCreators, connectItemView } from '../../../../../../metafox/user/src/hocs/connectUserItem';
import ItemView from './ItemView';

const Enhancer = connect((state: GlobalState) => ({
  itemActionMenu: state._resourceMenus.user.user.itemActionMenu.items
}))(ItemView);

export default connectItemView(Enhancer, actionCreators);
