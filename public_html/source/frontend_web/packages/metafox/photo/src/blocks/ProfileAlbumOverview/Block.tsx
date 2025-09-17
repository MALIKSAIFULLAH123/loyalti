/**
 * @type: block
 * name: photo_album.block.profilePhotoAlbumOverview
 * title: Profile Photo Albums
 * keywords: photo, profile
 * description: Display photo album items in profile
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const ProfilePhotoAlbumOverviewBlock = createBlock<ListViewBlockProps>({
  name: 'ProfilePhotoAlbumOverviewBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo_album',
    blockProps: {
      headerActions: [
        {
          label: 'all',
          to: '/user/:id/photo/album'
        }
      ]
    }
  },
  defaults: {
    title: 'Albums',
    itemView: 'photo_album.itemView.smallCard',
    dataSource: {
      apiUrl: '/photo-album',
      apiParams: 'user_id=:id&limit=6'
    },
    emptyPage: 'hide'
  }
});

export default ProfilePhotoAlbumOverviewBlock;
