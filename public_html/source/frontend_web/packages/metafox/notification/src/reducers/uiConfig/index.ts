const initialState = {
  sidebarHeader: {
    homepageHeader: {
      title: 'notifications',
      to: '/notification',
      icon: 'ico-bell2-o'
    }
  },
  menus: {
    notificationSettingsMenu: {
      items: [
        {
          label: 'Mark all as read',
          icon: 'ico-check',
          value: 'notification/markAllAsRead',
          testid: 'markAllAsRead'
        },
        {
          label: 'Notification settings',
          icon: 'ico-gear-o',
          value: 'notification/editNotificationSetting',
          testid: 'editNotificationSetting'
        },
        {
          label: 'Open Notifications',
          icon: 'ico-wallet-o',
          value: 'notification/browseNotifications',
          testid: 'browseNotifications'
        }
      ]
    }
  }
};

export default initialState;
