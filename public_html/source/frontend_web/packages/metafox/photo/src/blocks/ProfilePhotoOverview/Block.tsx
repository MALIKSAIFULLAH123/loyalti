/**
 * @type: block
 * name: photo.block.profilePhotoOverview
 * title: Profile Photos
 * keywords: photo, profile
 * description: Display photo items in profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'ProfilePhotoOverviewBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo',
    headerActions: [
      {
        label: 'all',
        to: '/user/:id/photo',
        showWhen: ['truthy', 'item.profile_menu_settings.photo']
      }
    ]
  },
  defaults: {
    title: 'Photos',
    itemView: 'photo.itemView.mainCard',
    emptyPage: 'hide',
    displayRowsLimit: 2,
    dataSource: {
      apiUrl: '/photo',
      apiParams: 'sort=latest&user_id=:id'
    }
  }
});
