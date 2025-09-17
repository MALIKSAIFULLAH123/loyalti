/**
 * @type: reducer
 * name: report
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';

const appName = 'report';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, {})
});
