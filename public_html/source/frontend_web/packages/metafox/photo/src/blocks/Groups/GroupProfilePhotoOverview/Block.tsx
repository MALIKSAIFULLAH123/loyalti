/**
 * @type: block
 * name: photo.block.groupProfilePhotoOverview
 * title: Group Profile Photos Overview
 * keywords: photo, profile
 * description: Display photo items overview in page's profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const ProfilePhotoOverviewBlock = createBlock<ListViewBlockProps>({
  name: 'ProfilePhotoOverviewBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo'
  },
  defaults: {
    emptyPage: 'hide',
    title: 'Photos',
    itemView: 'photo.itemView.smallCard',
    dataSource: {
      apiUrl: '/photo',
      apiParams: 'user_id=:id&limit=6'
    }
  }
});

export default ProfilePhotoOverviewBlock;
