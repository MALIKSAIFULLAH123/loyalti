/**
 * @type: block
 * name: photo.block.profileAlbums
 * title: Albums
 * keywords: photo
 * description: Display photo albums
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'ProfilePhotoAlbumListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo_album',
    dataSource: {
      apiUrl: '/photo-album',
      apiParams: 'sort=latest&user_id=:id'
    },
    gridVariant: 'listView'
  },
  defaults: {
    title: 'Albums',
    itemView: 'photo_album.itemView.profileCard',
    canLoadMore: true,
    canLoadSmooth: true
  }
});
