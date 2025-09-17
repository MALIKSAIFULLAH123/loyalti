import { HandleAction } from '@metafox/framework';
import { UserItemActions } from '../types';

export default function userItemActions(
  handleAction: HandleAction
): UserItemActions {
  return {
    toggleMenu: () => handleAction('toggleMenu'),
    editProfileHeaderAvatar: () => handleAction('editProfileHeaderAvatar'),
    presentMutualFriends: () => handleAction('friend/presentMutualFriends'),
    presentFriends: () => handleAction('friend/presentFriends'),
    addFriend: () => handleAction('user/addFriend'),
    acceptFriend: () => handleAction('user/acceptFriendRequest'),
    denyFriend: () => handleAction('user/denyFriendRequest'),
    chatWithFriend: (isMobile = false) =>
      handleAction('chat/room/openChatRoom', { isMobile }),
    cancelRequest: () => handleAction('user/cancelRequest'),
    unfriend: () => handleAction('user/unFriend')
  };
}
