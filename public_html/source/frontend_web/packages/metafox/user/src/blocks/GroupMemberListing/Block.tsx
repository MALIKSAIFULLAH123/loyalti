/**
 * @type: block
 * name: user.block.groupMembersListingBlock
 * title: Group's member
 * keywords: user
 * description: Display member items in profile page.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const UserListingBlock = createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  name: 'GroupUserListingBlock',
  overrides: {
    contentType: 'group_member'
  },
  defaults: {
    title: 'Group Members Listings',
    dataSource: { apiUrl: '/friend' },
    itemView: 'user.itemView.mainCard',
    canLoadMore: true,
    emptyPage: 'core.block.no_content'
  }
});

export default UserListingBlock;
