import { AppResource } from '@metafox/framework';

const initialState: AppResource = {
  actions: {
    searchItem: {
      placeholder: 'Search forum',
      pageUrl: '/forum/search'
    }
  },
  menus: {},
  forms: {}
};

export default initialState;
