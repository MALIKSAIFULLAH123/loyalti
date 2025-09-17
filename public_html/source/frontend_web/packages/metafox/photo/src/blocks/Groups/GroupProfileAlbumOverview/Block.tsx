/**
 * @type: block
 * name: album_photo.block.groupProfilePhotoAlbumOverview
 * title: Group's Photo Albums
 * keywords: photo
 * description: Display page's photo album items in profile
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'GroupProfilePhotoAlbumOverviewBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo_album'
  },
  defaults: {
    title: 'Albums',
    itemView: 'photo_album.itemView.smallCard',
    gridVariant: 'gridView',
    dataSource: {
      apiUrl: '/photo-album',
      apiParams: 'user_id=:id&limit=6'
    },
    emptyPage: 'hide'
  }
});
