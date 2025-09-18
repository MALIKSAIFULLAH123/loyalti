import { AppResource } from '@metafox/framework';

const initialState: AppResource = {
  actions: {
    searchItem: {
      placeholder: 'Search announcement',
      pageUrl: '/announcement/search'
    }
  },
  menus: {},
  forms: {}
};

export default initialState;
