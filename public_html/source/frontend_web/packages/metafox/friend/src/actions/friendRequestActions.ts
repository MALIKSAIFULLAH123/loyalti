import { HandleAction } from '@metafox/framework';
import { stopEvent } from '@metafox/utils';
import { FriendRequestActions } from '..';

export default function friendRequestActions(
  handleAction: HandleAction
): FriendRequestActions {
  return {
    deleteFriendRequest: stopEvent(() => handleAction('friend_request/deny')),
    acceptFriendRequest: stopEvent(() => handleAction('friend_request/accept')),
    cancelFriendRequest: stopEvent(() => handleAction('friend_request/cancel')),
    showMutualFriends: stopEvent(() => handleAction('friend/presentMutualFriends'))
  };
}
