import { PermissionShape, RoomType } from '../types';

function hasPermission(roles: string[], check: string[]): boolean {
  return !!roles.find(x => check.includes(x));
}

export default function hasPermissions(
  perms: PermissionShape[] = [],
  roles: string[] = [],
  roomType: string | false
): Record<string, any> {
  const result: Record<string, any> = perms
    .filter(perm => hasPermission(perm.roles, roles))
    .reduce((acc, { _id }) => {
      acc[_id] = true;

      return acc;
    }, {});

  result.canHideRoom = true;
  result.canEditNotification = true;
  result.canShowMembers = false;

  if (roomType) {
    // append logic to some case.
    result.canSearchMsg = true;
    result.canAddMembers = result[`add-user-to-any-${roomType}-room`];
    result.canStartCall = result[`start-call-${roomType}-room`];
    result.canStartVideoChat = result[`start-video-chat-${roomType}-room`];
    result.canLeaveRoom = result[`leave-${roomType}`];
    result.canDeleteRoom = result[`delete-${roomType}`];

    switch (roomType) {
      case RoomType.Public:
      case RoomType.Private:
        result.canShowMembers = true;
        break;
    }
  }

  return result;
}
