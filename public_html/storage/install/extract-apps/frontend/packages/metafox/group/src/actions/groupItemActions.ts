import { HandleAction } from '@metafox/framework';

export default function groupItemActions(handleAction: HandleAction) {
  return {
    joinGroup: () => handleAction('group/join'),
    unjoinGroup: () => handleAction('group/unjoin'),
    acceptInvite: () => handleAction('group/acceptInvite'),
    declineInvite: () => handleAction('group/declineInvite'),
    answerQuestion: () => handleAction('group/showMembershipDialog')
  };
}
