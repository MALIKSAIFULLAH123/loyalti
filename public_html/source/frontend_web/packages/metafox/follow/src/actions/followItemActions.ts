import { HandleAction } from '@metafox/framework';
import { FollowItemActions } from '../types';

export default function followItemActions(
  handleAction: HandleAction
): FollowItemActions {
  return {
    showMutualFriends: () => handleAction('friend/presentMutualFriends')
  };
}
