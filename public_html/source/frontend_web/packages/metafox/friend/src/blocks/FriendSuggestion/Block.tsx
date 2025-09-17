/**
 * @type: block
 * name: friend.block.suggestions
 * title: Friend Suggestions
 * keywords: friend
 * description: Display friend suggestions.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const FriendListingBlock = createBlock<ListViewBlockProps>({
  name: 'FriendSuggestions',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend',
    // hard prevent loadmore, this block currently not support
    canLoadMore: false
  },
  defaults: {
    dataSource: { apiUrl: '/friend/suggestion', apiParams: 'limit=4' },
    blockLayout: 'Profile - Side Contained (no gutter)',
    gridLayout: 'Profile Friends Small Lists',
    itemLayout: 'Profile Friends Small Lists',
    title: 'suggestions',
    itemView: 'friend.itemView.profileFriendCard',
    emptyPage: 'hide'
  }
});

export default FriendListingBlock;
