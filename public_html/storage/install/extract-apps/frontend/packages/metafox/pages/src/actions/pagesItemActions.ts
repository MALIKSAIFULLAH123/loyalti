import { HandleAction } from '@metafox/framework';

export default function pagesItemActions(handleAction: HandleAction) {
  return {
    likePage: () => handleAction('page/like'),
    unlikePage: () => handleAction('page/unlike'),
    approvePendingPage: () => handleAction('page/approvePendingPage'),
    declinePendingPage: () => handleAction('deleteItem'),
    acceptInvite: () => handleAction('page/acceptInvite'),
    declineInvite: () => handleAction('page/declineInvite'),
    unblockMember: () => handleAction('page/unblockFromPage'),
    presentMutualFriends: () => handleAction('friend/presentMutualFriends'),
    cancelInvitation: () => handleAction('page/cancelInvitation')
  };
}
