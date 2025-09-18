import { HandleAction } from '@metafox/framework';

export default function groupManagerActions(handleAction: HandleAction) {
  return {
    keepReportedPost: () => handleAction('group/keepReportedPost'),
    removeReportedPost: () => handleAction('group/removeReportedPost'),
    declinePendingPost: () => handleAction('feed/declinePendingPost'),
    approvePendingPost: () => handleAction('feed/approvePendingPost'),
    declinePendingRequest: () => handleAction('group/declinePendingRequest'),
    approvePendingRequest: () => handleAction('group/approvePendingRequest'),
    unBlockMember: () => handleAction('group/unblockFromGroup'),
    getListReport: () => handleAction('group/getListReport'),
    unMuteMember: () => handleAction('group/unmuteInGroup'),
    viewMemberQuestions: () => handleAction('group/viewMemberQuestions'),
    presentMutualFriends: () => handleAction('friend/presentMutualFriends')
  };
}
