const initialState = {
  sidebarHeader: {
    homepageHeader: {
      to: '/activity-point',
      title: 'activity_points',
      icon: 'ico-circle-o'
    }
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/activity-point',
          label: 'Points',
          tab: 'landing',
          testid: 'landing'
        },
        {
          to: '/activity-point/transaction',
          label: 'Transactions',
          tab: 'transaction',
          testid: 'transaction'
        },
        {
          as: 'button',
          icon: 'ico-plus',
          to: '/activity-point/purchase',
          label: 'Purchase Packages',
          testid: 'add',
          showWhen: ['and', ['truthy', 'session.loggedIn']],
          buttonProps: {
            fullWidth: true,
            color: 'primary',
            variant: 'contained'
          }
        }
      ]
    }
  }
};

export default initialState;
