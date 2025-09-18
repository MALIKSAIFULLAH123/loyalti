const initialState = {
  sidebarHeader: {
    homepageHeader: { title: 'forums', icon: 'ico-newspaper-alt', to: '/forum' }
  },
  sidebarCategory: {
    href: '/forum/category',
    title: 'communities',
    dataSource: { apiUrl: '/forum' }
  },
  sidebarSearch: {
    placeholder: 'search'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/forum',
          label: 'Top',
          tab: 'landing',
          icon: 'ico-newspaper-alt-o',
          testid: 'landing'
        },
        {
          to: '/forum/latest',
          label: 'Latest',
          tab: 'latest',
          icon: 'ico-folders-o',
          testid: 'latest'
        },
        {
          to: '/forum/my-threads',
          label: 'My Threads',
          tab: 'my_threads',
          testid: 'my_threads',
          icon: 'ico-folder-alt-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/forum/wiki',
          // eslint-disable-next-line quotes
          label: 'Wiki',
          tab: 'wiki',
          testid: 'wiki'
        },
        {
          as: 'sidebarButton',
          icon: 'ico-plus',
          to: '/forum/thread/add',
          label: 'New Thread',
          testid: 'add_thread',
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
