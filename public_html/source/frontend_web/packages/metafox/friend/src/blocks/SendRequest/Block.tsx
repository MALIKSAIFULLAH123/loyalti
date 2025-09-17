/**
 * @type: block
 * name: friend.block.SendRequest
 * title: Send Friend Requests
 * keywords: friend
 * description: Display send friend request items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'SendFriendRequestListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend_request',
    dataSource: { apiUrl: '/friend/request', apiParams: 'view=send' }
  },
  defaults: {
    itemView: 'friend_sentRequest.itemView.mainCard',
    blockLayout: 'Main Listings',
    gridLayout: 'Friend Request - Cards',
    clearDataOnUnMount: true
  }
});
