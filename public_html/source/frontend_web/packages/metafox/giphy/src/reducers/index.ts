/**
 * @type: reducer
 * name: giphy
 */

import { combineReducers, createEntityReducer } from '@metafox/framework';
import { APP_GIPHY } from '../constants';

export default combineReducers({
  entities: createEntityReducer(APP_GIPHY)
});
