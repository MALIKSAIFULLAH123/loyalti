/**
 * @type: embedView
 * name: user_relation_history.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import { connectItemView } from '../../../hocs/connectUserProfileItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
