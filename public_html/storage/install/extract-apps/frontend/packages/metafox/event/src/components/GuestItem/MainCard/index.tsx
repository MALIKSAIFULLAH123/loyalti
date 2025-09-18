/**
 * @type: itemView
 * name: event_invite.itemView.mainCard
 * chunkName: event
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/event/hocs/connectInviteItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
