/**
 * @type: reducer
 * name: link
 */

import { combineReducers, createEntityReducer } from '@metafox/framework';

const appName = 'link';

export default combineReducers({
  entities: createEntityReducer(appName)
});
