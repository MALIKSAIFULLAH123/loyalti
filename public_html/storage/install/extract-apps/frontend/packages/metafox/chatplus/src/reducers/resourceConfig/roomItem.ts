const state = {
  actions: {},
  menus: {
    itemActionMenu: {
      items: [
        {
          label: 'mark_as_read',
          icon: 'ico-envelope-opened-o',
          value: 'closeMenu, chatplus/room/markAsRead',
          testid: 'markAsRead',
          showWhen: [
            'and',
            ['truthy', 'subscription.alert'],
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'allowMsgNoOne']
          ]
        },
        {
          label: 'mark_unread',
          icon: 'ico-envelope-o',
          value: 'closeMenu, chatplus/room/markAsUnread',
          testid: 'markAsUnread',
          showWhen: [
            'and',
            ['falsy', 'subscription.alert'],
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'allowMsgNoOne']
          ]
        },
        {
          label: 'create_group',
          icon: 'ico-user2-plus-o',
          value: 'closeMenu, chatplus/room/createGroup',
          testid: 'createChatRoom',
          showWhen: [
            'and',
            ['truthy', 'perms.create-p'],
            ['eq', 'room.t', 'd'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'allowMsgNoOne'],
            ['falsy', 'isSelfChat']
          ]
        },
        {
          label: 'unfavorite_chat',
          icon: 'ico-heart',
          value: 'closeMenu, chatplus/room/unfavoriteRoom',
          testid: 'unfavoriteRoom',
          showWhen: [
            'and',
            ['truthy', 'settings.Favorite_Rooms'],
            ['truthy', 'subscription.f'],
            ['falsy', 'room.archived'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'isSelfChat']
          ]
        },
        {
          label: 'favorite_chat',
          icon: 'ico-heart-o',
          value: 'closeMenu, chatplus/room/favoriteRoom',
          testid: 'favoriteRoom',
          showWhen: [
            'and',
            ['truthy', 'settings.Favorite_Rooms'],
            ['falsy', 'subscription.f'],
            ['falsy', 'room.archived'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'isSelfChat']
          ]
        },
        {
          label: 'unarchive_chat',
          icon: 'ico-inbox',
          value: 'closeMenu, chatplus/room/unarchiveRoom',
          testid: 'unarchiveRoom',
          showWhen: [
            'and',
            ['truthy', 'perms.unarchive-room'],
            ['truthy', 'room.archived']
          ]
        },
        {
          label: 'archive_chat',
          icon: 'ico-inbox-o',
          value: 'closeMenu, chatplus/room/archiveRoom',
          testid: 'archiveRoom',
          showWhen: [
            'and',
            ['truthy', 'perms.archive-room'],
            ['falsy', 'room.archived'],
            ['neq', 'room.t', 'd']
          ]
        },
        {
          label: 'edit_info',
          icon: 'ico-info-circle-alt-o',
          value: 'closeMenu, chatplus/room/editInfoSettings',
          testid: 'editRoomSettings',
          showWhen: [
            'and',
            ['truthy', 'perms.edit-room'],
            ['neq', 'room.t', 'd']
          ]
        },
        {
          label: 'info',
          icon: 'ico-info-circle-alt-o',
          value: 'closeMenu, chatplus/room/presentSettings',
          testid: 'presentRoomSettings',
          showWhen: [
            'and',
            ['falsy', 'perms.edit-room'],
            ['neq', 'room.t', 'd']
          ]
        },
        {
          label: 'edit_notification',
          icon: 'ico-bell-o',
          value: 'closeMenu, chatplus/room/editNotificationSettings',
          testid: 'editNotificationSettings',
          showWhen: [
            'and',
            ['truthy', 'perms.edit-notification'],
            ['falsy', 'room.archived'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'allowMsgNoOne'],
            ['falsy', 'isSelfChat']
          ]
        },
        {
          label: 'hide_conversation',
          icon: 'ico-eye-off-o',
          value: 'closeMenu, chatplus/room/hideRoom',
          testid: 'hideRoom',
          showWhen: ['truthy', 'perms.hide-room']
        },
        {
          label: 'block_chat',
          icon: 'ico-ban',
          value: 'closeMenu, chatplus/room/blockChat',
          testid: 'blockChat',
          showWhen: [
            'and',
            ['eq', 'room.t', 'd'],
            ['falsy', 'isSelfChat'],
            ['falsy', 'subscription.blocker'],
            ['falsy', 'isMetaFoxBlocked']
          ]
        },
        {
          label: 'unblock_chat',
          icon: 'ico-ban',
          value: 'closeMenu, chatplus/room/unblockChat',
          testid: 'unblockChat',
          showWhen: [
            'and',
            ['eq', 'room.t', 'd'],
            ['falsy', 'isSelfChat'],
            ['truthy', 'subscription.blocker'],
            ['falsy', 'isMetaFoxBlocked']
          ]
        },
        {
          label: 'leave_room',
          icon: 'ico-signout',
          value: 'closeMenu, chatplus/room/leaveRoom',
          testid: 'leaveRoom',
          showWhen: [
            'and',
            ['truthy', 'perms.leave-room'],
            ['neq', 'room.t', 'd']
          ]
        },
        {
          label: 'delete',
          icon: 'ico-trash-o',
          value: 'closeMenu, chatplus/room/deleteRoom',
          testid: 'deleteRoom',
          showWhen: [
            'and',
            ['truthy', 'perms.delete-room'],
            ['falsy', 'room.isBotRoom']
          ]
        }
      ]
    }
  }
};

export default state;
