/**
 * @type: itemView
 * name: music_album.itemView.smallCard
 * chunkName: music
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/music/hocs/connectAlbumItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
