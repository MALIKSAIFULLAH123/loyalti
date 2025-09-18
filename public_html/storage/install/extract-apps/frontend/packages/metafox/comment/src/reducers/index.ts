/**
 * @type: reducer
 * name: comment
 */

import { combineReducers, createEntityReducer } from '@metafox/framework';

const appName = 'comment';

export default combineReducers({
  entities: createEntityReducer(appName)
});
