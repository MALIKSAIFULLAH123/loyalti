/**
 * @type: block
 * name: event.block.eventUpcomingBlock
 * title: Event Upcoming
 * keywords: event
 * description: Display event upcoming items.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const EventUpcomingBlock = createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'event',
    dataSource: { apiUrl: '/event' }
  },
  defaults: {
    title: 'Upcoming Event',
    blockProps: { variant: 'contained' },
    dataSource: { apiParams: 'sort=latest&limit=1' },
    itemView: 'event.itemView.upcomingCard',
    displayLimit: 1,
    gridContainerProps: { spacing: 0 },
    gridItemProps: { xs: 12, sm: 12, md: 12, lg: 12, xl: 12 }
  }
});

export default EventUpcomingBlock;
