/**
 * @type: block
 * name: user.block.userListingBlock
 * title: User Items
 * keywords: user
 * description: Display user items
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const UserListingBlock = createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  name: 'UserListingBlock',
  overrides: {
    dataSource: {
      apiUrl: '/user'
    },
    contentType: 'user'
  },
  defaults: {
    title: 'members',
    itemView: 'user.itemView.mainCard',
    blockLayout: 'Main Listings',
    gridLayout: 'Friend - Cards',
    itemLayout: 'Friend - Cards'
  }
});

export default UserListingBlock;
