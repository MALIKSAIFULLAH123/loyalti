/**
 * @type: block
 * name: photo.block.albumListingBlock
 * title: Albums
 * keywords: photo
 * description: Display photo albums
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'PhotoAlbumListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    title: 'Albums',
    itemView: 'photo_album.itemView.mainCard'
  }
});
