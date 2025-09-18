/**
 * @type: itemView
 * name: music_song.itemView.mainCard
 * chunkName: music
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/music/hocs/connectSongItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
