/**
 * @type: reducer
 * name: activitypoint
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';

const appName = 'activitypoint';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig)
});
