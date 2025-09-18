import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { getMyStickerSet } from '../selectors';
import { AppState } from '../types';

export default function useMyStickerSet() {
  return useSelector<GlobalState, AppState['myStickerSet']>(state =>
    getMyStickerSet(state)
  );
}
