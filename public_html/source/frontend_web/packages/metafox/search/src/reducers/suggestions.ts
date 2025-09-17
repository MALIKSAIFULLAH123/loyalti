import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['suggestions']>, action) => {
  switch (action.type) {
    case 'suggestions/FULFILL': {
      const { text, data } = action.payload;

      if (!text) return draft;

      draft[text] = { data, loaded: true };
      break;
    }
    default:
      return draft;
  }
}, {});
