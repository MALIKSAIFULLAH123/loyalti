/**
 * @type: block
 * name: photo.block.groupProfilePhotoListing
 * title: Group Profile Photos
 * keywords: photo, profile
 * description: Display photo items in page's profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const ProfilePhotoOverviewBlock = createBlock<ListViewBlockProps>({
  name: 'GroupProfilePhotoListing',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo'
  },
  defaults: {
    title: 'Photos',
    itemView: 'photo.itemView.smallCard',
    blockProps: { variant: 'plained' },
    gridContainerProps: { spacing: 1 },
    gridItemProps: { xs: 1, sm: 6, md: 6, lg: 6, xl: 6 },
    displayLimit: 6,
    emptyPage: 'core.block.no_content',
    dataSource: {
      apiUrl: '/photo',
      apiParams: 'user_id=:id&limit=6'
    }
  }
});

export default ProfilePhotoOverviewBlock;
