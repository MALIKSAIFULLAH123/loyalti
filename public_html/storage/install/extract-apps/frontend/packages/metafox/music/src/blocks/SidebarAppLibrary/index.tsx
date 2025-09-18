/**
 * @type: itemView
 * name: save_playlist.itemView.mainCard
 * title: Main Card
 * chunkName: music
 */
import {
  actionCreators,
  connectItemView
} from '../../hocs/connectPlaylistItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
