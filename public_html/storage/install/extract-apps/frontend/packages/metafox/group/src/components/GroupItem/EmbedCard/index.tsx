/**
 * @type: embedView
 * name: group.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import {
  actionCreators,
  connectItemView
} from '../../../hocs/connectGroupItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
