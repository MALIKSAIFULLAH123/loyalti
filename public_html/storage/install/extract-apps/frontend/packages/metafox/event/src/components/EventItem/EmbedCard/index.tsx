/**
 * @type: embedView
 * name: event.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/event/hocs/connectEventItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
