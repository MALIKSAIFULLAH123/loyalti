import { HandleAction } from '@metafox/framework';

export default function blockedUserActions(handleAction: HandleAction) {
  return {
    unblockItem: () => handleAction('unblockItem')
  };
}
