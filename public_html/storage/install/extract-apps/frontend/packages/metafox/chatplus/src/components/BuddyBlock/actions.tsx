import { MenuItemShape } from '@metafox/ui';

export function actionsOnlineFriend(): MenuItemShape[] {
  return [
    {
      label: 'online',
      icon: 'ico-circle',
      color: 'success',
      value: 'closeMenu, chatplus/setUserStatus:online',
      testid: 'status_online',
      item_name: 'online'
    },
    {
      label: 'away',
      icon: 'ico-circle',
      color: 'warning',
      value: 'closeMenu, chatplus/setUserStatus:away',
      testid: 'status_away',
      item_name: 'away'
    },
    {
      label: 'busy',
      icon: 'ico-circle',
      color: 'danger',
      value: 'closeMenu, chatplus/setUserStatus:busy',
      testid: 'status_busy',
      item_name: 'busy'
    },
    {
      label: 'invisible',
      icon: 'ico-circle',
      color: 'gray',
      value: 'closeMenu, chatplus/setUserStatus:invisible',
      testid: 'status_offline',
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
    },
    {
      label: 'archived_chats',
      icon: 'ico-inbox-o',
      value: 'closeMenu, chatplus/archivedChatMode',
      testid: 'archivedChatMode'
    }
  ];
}
