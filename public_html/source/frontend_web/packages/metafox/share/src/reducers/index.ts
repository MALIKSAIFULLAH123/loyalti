/**
 * @type: reducer
 * name: share
 */

import { combineReducers, createEntityReducer } from '@metafox/framework';
import shareOptions from './shareOptions';

const appName = 'share';

export default combineReducers({
  entities: createEntityReducer(appName),
  shareOptions
});
