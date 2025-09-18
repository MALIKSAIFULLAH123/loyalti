const initialState = {
  sidebarHeader: {
    homepageHeader: {
      title: 'saved',
      icon: 'ico-compose-alt',
      to: '/saved'
    }
  },
  sidebarCategory: {
    dataSource: { apiUrl: '/saveditems-collection' },
    href: '/saved/collection',
    title: 'collections'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/saved',
          label: 'Saved',
          icon: 'ico-compose-alt',
          testid: 'landing',
          tab: 'landing'
        },
        {
          to: '/saved/collections',
          label: 'My Collections',
          testid: 'collections',
          icon: 'ico-folder-star-o',
          tab: 'collection'
        }
      ]
    }
  }
};

export default initialState;
