import { HandleAction } from '@metafox/framework';

export default function otherSeventActions(handleAction: HandleAction) {
  return {
    showMutualFriends: () => handleAction('friend/presentMutualFriends')
  };
}
