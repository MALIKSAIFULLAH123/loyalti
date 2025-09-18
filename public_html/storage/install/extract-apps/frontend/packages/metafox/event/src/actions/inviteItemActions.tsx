import { HandleAction } from '@metafox/framework';

export default function inviteItemActions(handleAction: HandleAction) {
  return {
    approveCoHostInvite: () => handleAction('event/approveCoHostInvite'),
    denyCoHostInvite: () => handleAction('event/denyCoHostInvite'),
    cancelInvitation: onSuccess =>
      handleAction('event/cancelInvitation', { onSuccess }),
    interestedEvent: () => handleAction('interestedEvent'),
    notInterestedEvent: () => handleAction('notInterestedEvent'),
    joinEvent: () => handleAction('joinEvent'),
    cancelHostInvitation: onSuccess =>
      handleAction('cancelHostInvitation', { onSuccess }),
    removeHost: onSuccess => handleAction('event/removeHost', { onSuccess }),
    removeMemberGuest: onSuccess =>
      handleAction('event_member/removeMember', { onSuccess }),
      showMutualFriends: () => handleAction('friend/presentMutualFriends')
  };
}
