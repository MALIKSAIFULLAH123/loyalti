/**
 * @type: itemView
 * name: poll.itemView.smallCard
 * chunkName: poll
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/poll/hocs/connectPollItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
