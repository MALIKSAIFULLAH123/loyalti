/**
 * @type: block
 * name: photo.block.pageProfilePhotoListing
 * title: Page Profile Photos Listing
 * keywords: photo, profile
 * description: Display photo items in page's profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'PageProfilePhotoListing',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo'
  },
  defaults: {
    title: 'Photos',
    itemView: 'photo.itemView.smallCard',
    blockProps: { variant: 'plained' }
  }
});
