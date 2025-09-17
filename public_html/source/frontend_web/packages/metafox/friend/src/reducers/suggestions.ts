import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['suggestions']>, action) => {
  switch (action.type) {
    case 'friend/suggestions/LOAD': {
      const { text, data } = action.payload;

      draft[`:${text}`] = { data, loaded: false, text };
      break;
    }
    case 'friend/suggestions/FULFILL': {
      const { text, data } = action.payload;

      draft[`:${text}`] = { data, loaded: true, text };
      break;
    }
    default:
      return draft;
  }
}, {});
