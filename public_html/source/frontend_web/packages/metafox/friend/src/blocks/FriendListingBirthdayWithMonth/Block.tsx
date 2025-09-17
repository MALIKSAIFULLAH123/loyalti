/**
 * @type: block
 * name: friend.block.friendBirthday
 * title: Friends Birthday
 * keywords: friend
 * description: Display listing friend birthday.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

const FriendListingBlock = createBlock<ListViewBlockProps>({
  name: 'FriendListing',
  extendBlock: Base,
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
