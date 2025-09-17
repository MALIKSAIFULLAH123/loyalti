import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce(
  (draft: Draft<AppState['stepListing']>, action) => {
    switch (action.type) {
      case 'gettingStartedStep/listing/FULFILL':
        draft.data = [...draft.data, ...action.payload.data];
        draft.loaded = true;
        draft.total = action.payload.total;
        draft.page = action.payload.page;
        break;

      default:
        return draft;
    }
  },
  {
    data: [],
    loaded: false
  }
);
