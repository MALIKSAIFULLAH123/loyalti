import { HandleAction } from '@metafox/framework';
import { FriendListActions } from '../types';

export default function friendListActions(
  handleAction: HandleAction
): FriendListActions {
  return {
    presentMutualFriends: () => handleAction('friend/presentMutualFriends')
  };
}
