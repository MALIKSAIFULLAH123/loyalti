import { AppResource } from '@metafox/framework';

const actionMenu = {
  items: [
    {
      label: 'Mask as Read',
      icon: 'ico-check',
      testid: 'mark_read',
      value: 'notification/markAsRead'
    },
    {
      label: 'Delete',
      icon: 'ico-trash',
      testid: 'delete',
      value: 'notification/deleteItem',
      className: 'itemDelete'
    }
  ]
};

const initialState: AppResource = {
  actions: {
    searchItem: {
      pageUrl: '/notification/search',
      placeholder: 'Search notification'
    },
    viewAll: {
      apiUrl: '/notification'
    },
    viewItem: {
      apiUrl: '/notification/:id'
    }
  },
  menus: {
    itemActionMenu: actionMenu,
    detailActionMenu: actionMenu
  }
};

export default initialState;
