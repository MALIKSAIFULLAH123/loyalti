/**
 * @type: itemView
 * name: music_playlist.itemView.smallCard
 * chunkName: music
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/music/hocs/connectPlaylistItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
