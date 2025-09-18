/**
 * @type: itemView
 * name: page.itemView.mainCard
 * chunkName: page
 */

import { actionCreators, connectItemView } from '../../../hocs/connectPageItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
