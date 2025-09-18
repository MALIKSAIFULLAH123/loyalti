import { AppResource } from '@metafox/framework';

const initialState: AppResource = {
  actions: {
    searchItem: {
      placeholder: 'Search thread',
      pageUrl: '/forum/thread/search'
    },
    viewAll: {
      apiUrl: '/forum-thread'
    },
    viewItem: {
      pageUrl: '/forum/thread/:id',
      apiUrl: '/forum-thread/:id'
    },
    editItem: {
      pageUrl: '/forum/thread/edit/:id',
      apiUrl: '/forum-thread/form/:id'
    },
    addItem: {
      pageUrl: '/forum/thread/add',
      apiUrl: '/forum-thread/form'
    }
  },
  menus: {},
  forms: {}
};

export default initialState;
