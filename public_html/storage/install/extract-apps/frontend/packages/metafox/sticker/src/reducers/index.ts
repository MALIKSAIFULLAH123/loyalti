/**
 * @type: reducer
 * name: sticker
 */
import { combineReducers, createEntityReducer } from '@metafox/framework';
import myStickerSet from './myStickerSet';
import stickerSet from './stickerSet';

const appName = 'sticker';

export default combineReducers({
  entities: createEntityReducer(appName),
  stickerSet,
  myStickerSet
});
