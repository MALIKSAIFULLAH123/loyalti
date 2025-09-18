/**
 * @type: block
 * name: music.block.musicAlbumListingBlock
 * title: Music Albums
 * keywords: music
 * description: Display listing music albums
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const MusicAlbumListingBlock = createBlock<ListViewBlockProps>({
  name: 'MusicAlbumListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    itemView: 'music_album.itemView.profileCard'
  }
});

export default MusicAlbumListingBlock;
