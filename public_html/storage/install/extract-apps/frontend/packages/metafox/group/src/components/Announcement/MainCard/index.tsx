/**
 * @type: itemView
 * name: group_announcement.itemView.mainCard
 * chunkName: group
 */

import { connectItemView } from '../../../hocs/connectGroupItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
