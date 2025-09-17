/**
 * @type: block
 * name: album_photo.block.pageProfilePhotoAlbumOverview
 * title: Page's Photo Albums
 * keywords: photo, profile
 * description: Display page's photo album items in profile
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'PageProfilePhotoAlbumOverviewBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo_album'
  },
  defaults: {
    title: 'Albums',
    itemView: 'photo_album.itemView.smallCard',
    blockProps: { variant: 'plained' },
    gridContainerProps: { spacing: 1 },
    gridItemProps: { xs: 12, sm: 6, md: 6, lg: 6, xl: 6 },
    gridVariant: 'gridView',
    dataSource: {
      apiUrl: '/photo-album',
      apiParams: 'user_id=:id&limit=6'
    }
  }
});
