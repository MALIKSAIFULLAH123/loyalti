/**
 * @type: block
 * name: friend.block.profileFriendRequestOverview
 * title: Profile Friend Requests
 * keywords: friend, profile
 * description: Display profile friend requests.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const ProfileFriendRequestOverviewBlock = createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'friend_request',
    dataSource: { apiUrl: '/friend/request', apiParams: 'view=request&limit=6' }
  },
  defaults: {
    title: 'Friend',
    gridContainerProps: {
      spacing: 0
    },
    blockProps: {
      variant: 'contained',
      noHeader: false
    },
    gridItemProps: { xs: 12, md: 12 },
    itemView: 'friend_pendingRequest.itemView.smallCard'
  }
});

export default ProfileFriendRequestOverviewBlock;
