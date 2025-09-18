/**
 * @type: block
 * name: event.block.groupEventListingBlock
 * title: Group's Events
 * keywords: event, group
 * description: Display group's events.
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'event'
  },
  defaults: {
    title: 'Events',
    blockProps: { variant: 'contained' },
    itemView: 'event.itemView.mainCard',
    displayLimit: 1,
    gridContainerProps: { spacing: 2 },
    gridItemProps: { xs: 12, sm: 6, md: 4, lg: 4, xl: 4 },
    gridVariant: 'gridView',
    dataSource: {
      apiUrl: '/event',
      apiParams: 'user_id=:id&limit=6'
    },
    emptyPage: 'hide'
  }
});
