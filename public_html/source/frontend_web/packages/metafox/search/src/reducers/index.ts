/**
 * @type: reducer
 * name: search
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import recentSearch from './recentSearch';
import suggestions from './suggestions';
import uiConfig from './uiConfig';

const appName = 'search';

export default combineReducers({
  entities: createEntityReducer(appName),
  recentSearch,
  suggestions,
  uiConfig: createUIReducer(appName, uiConfig)
});
