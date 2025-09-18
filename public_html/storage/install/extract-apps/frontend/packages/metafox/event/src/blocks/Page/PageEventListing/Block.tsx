/**
 * @type: block
 * name: event.block.pagesEventListingBlock
 * title: Page's Events
 * keywords: page
 * description: Display page's events.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview'
});
