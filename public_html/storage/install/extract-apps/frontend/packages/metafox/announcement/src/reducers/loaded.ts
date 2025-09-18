import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['loaded']>, action) => {
  switch (action.type) {
    case 'announcement/loaded':
      draft = action.payload || true;
      break;

    default:
      return draft;
  }
}, false);
