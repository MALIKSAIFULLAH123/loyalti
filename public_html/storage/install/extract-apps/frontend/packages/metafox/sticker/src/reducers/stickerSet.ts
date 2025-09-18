import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['stickerSet']>, action) => {
  switch (action.type) {
    case 'sticker/stickerSet/FULFILL':
      draft.data = action.payload.data;
      draft.loaded = true;
      draft.loading = false;
      break;
    default:
      return draft;
  }
}, {
  data: [],
  loaded: false
});