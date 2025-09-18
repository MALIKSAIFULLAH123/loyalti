import { HandleAction } from '@metafox/framework';

export default function managePage(handleAction: HandleAction) {
  return {
    approvePendingPage: () => handleAction('page/approvePendingPage'),
    declinePendingPage: () => handleAction('deleteItem')
  };
}
