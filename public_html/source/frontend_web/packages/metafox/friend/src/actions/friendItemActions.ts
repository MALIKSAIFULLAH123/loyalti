import { HandleAction } from '@metafox/framework';
import { FriendItemActions } from '../types';

export default function friendItemActions(
  handleAction: HandleAction
): FriendItemActions {
  return {
    showMutualFriends: () => handleAction('friend/presentMutualFriends'),
    showFriends: () => handleAction('friend/presentFriends')
  };
}
