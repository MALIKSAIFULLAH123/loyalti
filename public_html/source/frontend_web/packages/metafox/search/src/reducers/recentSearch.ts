import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce(
  (draft: Draft<AppState['recentSearch']>, action) => {
    switch (action.type) {
      case 'recentSearch/FULFILL': {
        const { data } = action.payload;
        draft.data = data ?? [];
        draft.loaded = true;
        break;
      }
      default:
        return draft;
    }
  },
  { data: [], loaded: false }
);
