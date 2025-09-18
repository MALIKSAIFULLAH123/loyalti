/**
 * @type: itemView
 * name: music_song.itemView.smallCard
 * chunkName: music
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/music/hocs/connectSongItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
