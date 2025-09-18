import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import { StickerSetShape } from '../types';

export const getMyStickerSet = (state: GlobalState) =>
  state.sticker.myStickerSet;

export const getStickerSets = (
  state: GlobalState,
  ids: string[]
): StickerSetShape[] => (ids ? ids.map(id => get(state, id)) : []);

export const getAllStickerSet = (state: GlobalState) =>
  state.sticker.stickerSet;
