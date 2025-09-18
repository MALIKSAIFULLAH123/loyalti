import { MenuItemShape } from '@metafox/ui';

export function actionsOnlineFriend(): MenuItemShape[] {
  return [
    {
      label: 'create_new_group',
      icon: 'ico-user2-three-o',
      value: 'closeMenu, chatplus/room/newGroup',
      testid: 'createChatRoom',
      showWhen: [
        'or',
        ['truthy', 'perms.create-p'],
        ['truthy', 'perms.create-c']
      ]
    },
    {
      label: 'new_conversation',
      icon: 'ico-compose',
      value: 'closeMenu, chatplus/buddyPanel/createChatRoomFromDock',
      testid: 'createChatRoomFromDock',
      showWhen: ['truthy', 'perms.create-p']
    },
    {
      label: 'more_options',
      icon: 'ico-dottedmore-o',
      testid: 'more',
      disablePortal: false,
      behavior: 'more',
      items: [
        {
          label: 'online',
          icon: 'ico-circle',
          color: 'success',
          value: 'closeMenu, chatplus/setUserStatus:online',
          testid: 'status_online',
          name: 'status_user',
          item_name: 'online'
        },
        {
          label: 'away',
          icon: 'ico-circle',
          color: 'warning',
          value: 'closeMenu, chatplus/setUserStatus:away',
          testid: 'status_away',
          name: 'status_user',
          item_name: 'away'
        },
        {
          label: 'busy',
          icon: 'ico-circle',
          color: 'danger',
          value: 'closeMenu, chatplus/setUserStatus:busy',
          testid: 'status_busy',
          name: 'status_user',
          item_name: 'busy'
        },
        {
          label: 'invisible',
          icon: 'ico-circle',
          color: 'gray',
          value: 'closeMenu, chatplus/setUserStatus:invisible',
          testid: 'status_offline',
          name: 'status_user',
          item_name: 'offline'
        },
        {
          as: 'divider',
          testid: 'status_divider'
        },
        {
          label: 'settings',
          icon: 'ico-gear-o',
          value: 'closeMenu, chatplus/editUserPreferences',
          testid: 'editUserPreferences'
        }
      ]
    }
  ];
}
