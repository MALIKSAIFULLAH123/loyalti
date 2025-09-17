/**
 * @type: block
 * name: friend.block.FriendListListing
 * title: Group of Friends Listing
 * keywords: friend
 * description: Display group of friends.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'FriendListListing',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend_list',
    dataSource: { apiUrl: '/friend/list' },
    showWhen: ['truthy', 'acl.friend.friend_list.view']
  },
  defaults: {
    title: 'friend_list',
    blockLayout: 'Friend List Side Block',
    gridLayout: 'Friend List - Small List',
    gridItemProps: { xs: 12, md: 12 },
    itemView: 'friend_list.itemView.mainCard',
    emptyPage: 'core.block.no_content_with_icon',
    emptyPageProps: {
      description: 'no_friends_to_show_description',
      title: 'no_friends_to_show',
      image: 'ico-user-circle-o'
    }
  }
});
