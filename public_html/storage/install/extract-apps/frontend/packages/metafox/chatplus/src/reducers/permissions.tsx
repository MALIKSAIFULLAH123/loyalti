import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['permissions']>, action) => {
  switch (action.type) {
    case 'chatplus/init':
      draft.values = action.payload.permissions;
      draft.data = action.payload.permissions.reduce((acc, item) => {
        acc[item._id] = item.roles;

        return acc;
      }, {});
      break;
    default:
      return draft;
  }
}, {
  data: {},
  values: []
});
