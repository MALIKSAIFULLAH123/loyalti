/**
 * @type: block
 * name: pages.block.profilePagesListingOverview
 * title: Profile Pages
 * keywords: page, profile
 * description:
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const PagesListingBlock = createBlock<ListViewBlockProps>({
  name: 'PagesListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'pages',
    dataSource: { apiUrl: '/pages', apiParams: 'user_id=:id&limit=2' }
  },
  defaults: {
    title: 'Pages',
    blockProps: { variant: 'contained' },
    itemView: 'pages.itemView.smallCard',
    gridContainerProps: { spacing: 0 },
    gridItemProps: { xs: 12, sm: 12, md: 12 }
  }
});

export default PagesListingBlock;
