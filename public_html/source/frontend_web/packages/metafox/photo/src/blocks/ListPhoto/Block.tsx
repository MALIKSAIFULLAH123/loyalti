/**
 * @type: block
 * name: photo.block.photoListingBlock
 * title: Photos
 * keywords: photo
 * description: Display photo items
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'PhotoListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'photo'
  },
  defaults: {
    title: 'Photos',
    dataSource: { apiUrl: '/photo' },
    itemView: 'photo.itemView.mainCard'
  }
});
