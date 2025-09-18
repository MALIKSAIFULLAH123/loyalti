import { HandleAction } from '@metafox/framework';

export default function savedItemActions(handleAction: HandleAction) {
  return {
    showMutualFriends: () => handleAction('friend/presentMutualFriends')
  };
}
