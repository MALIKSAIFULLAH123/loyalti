/**
 * @type: itemView
 * name: event.itemView.hostSmallCard
 * chunkName: event
 */

import otherEventActions from '@metafox/event/actions/otherActions';
import { connectItemView } from '@metafox/framework';
import ItemView from './ItemView';

export default connectItemView(ItemView, otherEventActions);
