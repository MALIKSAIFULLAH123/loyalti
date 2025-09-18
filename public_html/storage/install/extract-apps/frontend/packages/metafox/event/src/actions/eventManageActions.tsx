import { HandleAction } from '@metafox/framework';

export function eventManageActions(handleAction: HandleAction) {
  return {
    approvePendingPost: () => handleAction('feed/approvePendingPost'),
    declinePendingPost: () => handleAction('feed/declinePendingPost')
  };
}
