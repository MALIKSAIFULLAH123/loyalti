import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['data']>, action) => {
  switch (action.type) {
    case 'reaction/bootstrap':
      draft.ids = action.payload.ids;
      draft.reactions = action.payload.reactions;
      draft.unreactedItem = action.payload.unreactedItem;
  }
}, {
  ids: [],
  items: []
});