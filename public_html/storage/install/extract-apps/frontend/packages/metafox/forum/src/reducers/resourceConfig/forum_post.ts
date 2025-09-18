import { AppResource } from '@metafox/framework';

const initialState: AppResource = {
  actions: {
    searchItem: {
      pageUrl: '/forum/search',
      placeholder: 'Search forum'
    }
  },
  menus: {},
  forms: {}
};

export default initialState;
