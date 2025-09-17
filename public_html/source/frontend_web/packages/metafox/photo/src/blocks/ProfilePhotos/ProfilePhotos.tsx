/**
 * @type: block
 * name: photo.block.profilePhotos
 * title: Profile Photos
 * keywords: photo, profile
 * description: Display photo items
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'ProfilePhotoListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo'
  },
  defaults: {
    title: 'Photos',
    dataSource: {
      apiUrl: '/photo',
      apiParams: 'sort=latest&user_id=:id'
    },
    itemView: 'photo.itemView.profileCard'
  }
});
