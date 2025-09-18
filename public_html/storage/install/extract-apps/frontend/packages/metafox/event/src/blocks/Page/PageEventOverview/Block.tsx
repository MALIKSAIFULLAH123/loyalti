/**
 * @type: block
 * name: event.block.pagesEventOverviewBlock
 * title: Page's Event Overview
 * keywords: page
 * description: Display page's event overview.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'event',
    emptyPage: 'hide'
  },
  defaults: {
    title: 'events',
    itemView: 'event.itemView.upcomingCard',
    displayLimit: 1,
    dataSource: {
      apiUrl: '/event',
      apiParams: 'user_id=:id&limit=6&when=upcoming'
    }
  }
});
