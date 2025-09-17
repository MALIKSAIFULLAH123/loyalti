import { AppResource } from '@metafox/framework';

const initialState: AppResource = {
  actions: {
    unhideItem: {
      apiUrl: '/feed/hide-all/:id?item_type=:module_name',
      apiMethod: 'delete'
    },
    undoUnhideItem: {
      apiUrl: '/feed/hide-all/:id?item_type=:module_name',
      apiMethod: 'post'
    }
  }
};

export default initialState;
