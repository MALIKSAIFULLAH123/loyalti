/**
 * @type: reducer
 * name: attachment
 */

import { combineReducers, createEntityReducer } from '@metafox/framework';

const appName = 'attachment';

export default combineReducers({
  entities: createEntityReducer(appName)
});
