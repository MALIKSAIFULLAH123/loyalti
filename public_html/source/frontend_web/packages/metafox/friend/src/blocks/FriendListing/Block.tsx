/**
 * @type: block
 * name: friend.block.friends
 * title: Friends
 * keywords: friend
 * description: Display listing friends.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const FriendListingBlock = createBlock<ListViewBlockProps>({
  name: 'FriendListing',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend'
  },
  defaults: {
    title: 'Friend',
    dataSource: { apiUrl: '/friend' },
    emptyPage: 'hide',
    itemView: 'friend.itemView.mainCard',
    blockLayout: 'Main Listings',
    gridLayout: 'Friend List - Small List'
  }
});

export default FriendListingBlock;
