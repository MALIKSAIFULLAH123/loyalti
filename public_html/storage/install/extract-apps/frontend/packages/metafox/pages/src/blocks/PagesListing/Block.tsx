/**
 * @type: block
 * name: pages.block.pagesListingBlock
 * title: Pages
 * keywords: page
 * description: Show pages listing
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import { APP_PAGE } from '@metafox/pages/constant';

export default createBlock<ListViewBlockProps>({
  name: 'PagesListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'page'
  },
  defaults: {
    title: 'Pages',
    itemView: 'page.itemView.mainCard',
    dataSource: { apiUrl: `/${APP_PAGE}` }
  }
});
