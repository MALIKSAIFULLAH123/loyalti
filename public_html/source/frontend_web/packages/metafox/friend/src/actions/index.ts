import { stopEvent } from '@metafox/utils';
import { HandleAction } from '@metafox/framework';
import { FriendItemActions } from '../types';

export default function friendItemActions(
  handleAction: HandleAction
): FriendItemActions {
  return {
    deleteFriendRequest: stopEvent(() =>
      handleAction('friend/deleteFriendRequest')
    ),
    acceptFriendRequest: stopEvent(() =>
      handleAction('friend/acceptFriendRequest')
    ),
    showMutualFriends: stopEvent(() =>
      handleAction('friend/presentMutualFriends')
    ),
    showFriends: stopEvent(() => handleAction('friend/presentFriends'))
  };
}
