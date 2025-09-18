/**
 * @type: block
 * name: music.block.musicSongListingBlock
 * title: Music Songs
 * keywords: music
 * description: Display listing music
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const MusicSongListingBlock = createBlock<ListViewBlockProps>({
  name: 'MusicSongListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    itemView: 'music_song.itemView.profileCard'
  }
});

export default MusicSongListingBlock;
