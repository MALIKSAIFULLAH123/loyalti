/**
 * @type: embedView
 * name: user.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import { actionCreators, connectItemView } from '../../../hocs/connectUserItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
