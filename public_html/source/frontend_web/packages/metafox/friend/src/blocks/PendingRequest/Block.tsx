/**
 * @type: block
 * name: friend.block.pendingRequest
 * title: Pending Friend Requests
 * keywords: friend
 * description: Display pending friend requests.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const PendingFriendRequestListingBlock = createBlock<ListViewBlockProps>({
  name: 'PendingFriendRequestListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    title: 'Pending request',
    itemView: 'friend_pendingRequest.itemView.smallCard',
    emptyPage: 'core.block.no_content',
    loadMoreType: 'button',
    canLoadMore: 1,
    canLoadSmooth: 1,
    blockLayout: 'Clean - Body Only',
    gridLayout: 'Friend - Pending Lists',
    itemLayout: 'Friend - Pending Lists',
    dataSource: {
      apiUrl: '/friend/request',
      apiParams: 'view=pending'
    },
    contentType: 'friend_request',
    emptyPageProps: {
      noHeader: true,
      title: 'no_requests'
    }
  }
});

export default PendingFriendRequestListingBlock;
