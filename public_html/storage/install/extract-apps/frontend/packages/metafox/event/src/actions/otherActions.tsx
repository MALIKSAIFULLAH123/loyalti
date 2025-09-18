import { HandleAction } from '@metafox/framework';

export default function otherEventActions(handleAction: HandleAction) {
  return {
    showMutualFriends: () => handleAction('friend/presentMutualFriends')
  };
}
