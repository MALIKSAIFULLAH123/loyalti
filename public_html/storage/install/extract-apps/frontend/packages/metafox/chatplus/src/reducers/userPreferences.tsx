import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['userPreferences']>, action) => {
  switch (action.type) {
    case 'chatplus/init':
      Object.assign(draft, action.payload?.userPreferences);
      break;
    case 'chatplus/saveUserPreferences':
      return action.payload;
    default:
      return draft;
  }
}, {});
