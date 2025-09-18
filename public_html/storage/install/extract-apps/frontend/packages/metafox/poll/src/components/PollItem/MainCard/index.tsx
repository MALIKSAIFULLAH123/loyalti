/**
 * @type: itemView
 * name: poll.itemView.mainCard
 * chunkName: poll
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/poll/hocs/connectPollItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
