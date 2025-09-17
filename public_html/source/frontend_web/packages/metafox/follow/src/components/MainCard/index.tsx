/**
 * @type: itemView
 * name: follow.itemView.mainCard
 * chunkName: follow
 */

import ItemView from './ItemView';
import { actionCreators, connectItemView } from '../../hocs';

export default connectItemView(ItemView, actionCreators);
