/**
 * @type: itemView
 * name: pages.itemView.smallCard
 * chunkName: page
 */

import { actionCreators, connectItemView } from '../../../hocs/connectPageItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
