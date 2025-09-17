import { AppState } from '@metafox/feed/types';
import produce, { Draft } from 'immer';

export default produce(
  (draft: Draft<AppState['paging']>, action) => {
    switch (action.type) {
      case 'feed/pagingId': {
        draft.active = action.payload;
        break;
      }
    }
  },
  { active: '' }
);
