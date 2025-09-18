import { AppResource } from '@metafox/framework';

const state: AppResource = {
  actions: {
    deleteItem: {},
    editItem: {}
  },
  menus: {
    itemActionMenu: {
      items: [
        {
          icon: 'ico-phone-o',
          value: 'closeMenu, chatplus/room/startVoiceCall',
          testid: 'startVoiceCall',
          label: 'start_an_audio_call',
          showWhen: [
            'and',
            ['truthy', 'perms.start-call'],
            ['truthy', 'settings.Metafox_Enable_Voice_Call'],
            ['falsy', 'room.archived'],
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isReadOnly'],
            ['falsy', 'allowMsgNoOne'],
            ['falsy', 'isMuted'],
            [
              'or',
              ['and', ['neq', 'room.t', 'd'], ['gt', 'room.usersCount', 1]],
              ['eq', 'room.t', 'd']
            ]
          ]
        },
        {
          icon: 'ico-video',
          value: 'closeMenu, chatplus/room/startVideoChat',
          testid: 'startVideoChat',
          label: 'start_a_video_call',
          showWhen: [
            'and',
            ['truthy', 'perms.start-video-chat'],
            ['truthy', 'settings.Metafox_Enable_Video_Chat'],
            ['falsy', 'room.archived'],
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isReadOnly'],
            ['falsy', 'allowMsgNoOne'],
            ['falsy', 'isMuted'],
            [
              'or',
              ['and', ['neq', 'room.t', 'd'], ['gt', 'room.usersCount', 1]],
              ['eq', 'room.t', 'd']
            ]
          ]
        },
        {
          icon: 'ico-search-o',
          value: 'chatplus/room/openSearching',
          testid: 'search',
          label: 'search',
          showWhen: [
            'and',
            ['truthy', 'perms.search-msg'],
            [
              'or',
              ['truthy', 'starred'],
              ['truthy', 'pinned'],
              ['falsy', 'searching']
            ]
          ]
        },
        {
          icon: 'ico-search-o',
          value: 'chatplus/room/toggleSearching',
          testid: 'cancelSearch',
          label: 'cancel_search',
          showWhen: [
            'and',
            ['truthy', 'perms.search-msg'],
            ['truthy', 'searching'],
            ['falsy', 'starred'],
            ['falsy', 'pinned']
          ]
        },
        {
          icon: 'ico-comment-square-dots-o',
          value: 'chatplus/room/openInMessenger',
          testid: 'openInMessenger',
          label: 'open_in_messenger'
        },
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
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'allowMsgNoOne']
          ]
        },
        {
          label: 'undo_starred_messages',
          icon: 'ico-star-o',
          value: 'closeMenu, chatplus/room/toggleSearching',
          testid: 'unstarredMessages',
          showWhen: [
            'and',
            ['truthy', 'starred'],
            ['truthy', 'settings.Message_AllowStarring']
          ]
        },
        {
          label: 'starred_messages',
          icon: 'ico-star-o',
          value: 'closeMenu, chatplus/room/starredMessages',
          testid: 'starredMessages',
          showWhen: [
            'and',
            ['falsy', 'starred'],
            ['truthy', 'settings.Message_AllowStarring']
          ]
        },
        {
          label: 'pinned_messages',
          icon: 'ico-magic',
          value: 'closeMenu, chatplus/room/pinnedMessages',
          testid: 'unPinnedMessages',
          showWhen: [
            'and',
            ['falsy', 'pinned'],
            ['truthy', 'settings.Message_AllowPinning']
          ]
        },
        {
          label: 'undo_pinned_messages',
          icon: 'ico-magic',
          value: 'closeMenu, chatplus/room/toggleSearching',
          testid: 'pinnedMessages',
          showWhen: [
            'and',
            ['truthy', 'pinned'],
            ['truthy', 'settings.Message_AllowPinning']
          ]
        },
        {
          label: 'add_members',
          icon: 'ico-user3-plus-o',
          value: 'closeMenu, chatplus/room/addNewMembersPage',
          testid: 'addNewMembers',
          showWhen: [
            'and',
            ['truthy', 'perms.add-members'],
            ['falsy', 'room.archived'],
            ['neq', 'room.t', 'd']
          ]
        },
        {
          label: 'members',
          icon: 'ico-user2-three-o',
          value: 'closeMenu, chatplus/room/showPresentMembers',
          testid: 'presentRoomMembers',
          showWhen: ['truthy', 'perms.show-members']
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
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted']
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
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted']
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
            ['falsy', 'isSelfChat'],
            ['falsy', 'isBlocked'],
            ['falsy', 'isMuted'],
            ['falsy', 'allowMsgNoOne']
          ]
        },
        {
          label: 'hide_conversation',
          icon: 'ico-eye-off-o',
          value: 'closeMenu, chatplus/room/hideRoom',
          testid: 'hideRoom',
          showWhen: ['and', ['truthy', 'perms.hide-room']]
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
        },
        {
          label: 'more',
          icon: 'ico-dottedmore-vertical-o',
          testid: 'more',
          behavior: 'more'
        },
        {
          icon: 'ico-minus',
          value: 'chatplus/openRooms/minimize',
          testid: 'minimize',
          label: 'minimize',
          behavior: 'close'
        },
        {
          label: 'close',
          icon: 'ico-close',
          value: 'closeMenu, chatplus/closePanel',
          testid: 'closePanel',
          behavior: 'close'
        }
      ]
    }
  }
};

export default state;
