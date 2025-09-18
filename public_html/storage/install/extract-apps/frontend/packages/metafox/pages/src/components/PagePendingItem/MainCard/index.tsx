/**
 * @type: itemView
 * name: pages.itemView.pendingMainCard
 * chunkName: page
 */

import { connectItemView } from '@metafox/framework';
import ItemView from './ItemView';
import actionCreators from '../../../actions/managePage';

export default connectItemView(ItemView, actionCreators);
