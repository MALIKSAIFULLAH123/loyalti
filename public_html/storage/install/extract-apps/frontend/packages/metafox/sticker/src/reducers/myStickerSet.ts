import produce, { Draft } from 'immer';
import { difference } from 'lodash';
import { AppState } from '../types';

export default produce(
  (draft: Draft<AppState['myStickerSet']>, action) => {
    switch (action.type) {
      case 'sticker/myStickerSet/INIT':
        draft.loading = true;
        break;
      case 'sticker/myStickerSet/FULFILL':
        draft.data = action.payload.data;
        draft.loaded = true;
        draft.loading = false;
        break;
      case 'sticker/myStickerSet/addItem':
        draft.data.push(action.payload.data);
        break;
      case 'sticker/myStickerSet/removeItem':
        draft.data = difference(draft.data, [action.payload.data]);
        break;
      default:
        return draft;
    }
  },
  {
    data: [],
    loaded: false,
    loading: false
  }
);
