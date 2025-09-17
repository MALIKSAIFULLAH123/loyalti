/**
 * @type: block
 * name: user.block.pageMembersListingBlock
 * title: Page's member
 * keywords: user
 * description: Display member items in profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const UserListingBlock = createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  name: 'PageUserListingBlock',
  overrides: {
    dataSource: {
      apiUrl: '/page-member',
      apiParams: 'page_id=:id&view=all'
    },
    contentType: 'user'
  },
  defaults: {
    title: 'Page Members Listings',
    gridItemProps: {
      xs: 12,
      sm: 6,
      md: 4,
      lg: 4,
      xl: 3
    },
    blockProps: {
      marginBottom: '2',
      variant: 'plained'
    },
    gridContainerProps: {
      spacing: 2
    },
    itemView: 'user.itemView.mainCard',
    canLoadMore: true
  }
});

export default UserListingBlock;
