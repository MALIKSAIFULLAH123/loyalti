import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { getStickerSets } from '../selectors';
import { StickerSetShape } from '../types';

export default function useStickerSets(ids: string[]) {
  return useSelector<GlobalState, StickerSetShape[]>(state =>
    getStickerSets(state, ids)
  );
}
