/**
 * @type: itemView
 * name: shortcut.itemView.smallCard
 * chunkName: sidebarHome
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/user/hocs/connectShortcutItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
