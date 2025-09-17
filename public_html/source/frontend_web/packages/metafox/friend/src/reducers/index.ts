/**
 * @type: reducer
 * name: friend
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import suggestions from './suggestions';
import uiConfig from './uiConfig';

const appName = 'friend';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig),
  suggestions
});
