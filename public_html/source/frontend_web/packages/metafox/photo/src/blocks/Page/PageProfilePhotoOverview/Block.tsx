/**
 * @type: block
 * name: photo.block.pageProfilePhotoOverview
 * title: Page Profile Photos overview
 * keywords: photo, profile
 * description: Display photo items in page's profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'PageProfilePhotoOverview',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo',
    emptyPage: 'hide'
  },
  defaults: {}
});
