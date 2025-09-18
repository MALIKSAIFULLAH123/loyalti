/**
 * @type: block
 * name: event.block.eventListingBlock
 * title: Events
 * keywords: event
 * description: Display event items.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  defaults: {
    title: 'Events',
    itemView: 'event.itemView.mainCard'
  }
});
