/**
 * @type: embedView
 * name: poll.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/poll/hocs/connectPollItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators, { poll_answer: true });
