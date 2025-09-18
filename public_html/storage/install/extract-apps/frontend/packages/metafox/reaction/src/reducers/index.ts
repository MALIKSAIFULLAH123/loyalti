/**
 * @type: reducer
 * name: preaction
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import data from './data';
import reactedTabs from './reactedTabs';

const appName = 'preaction';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, {}),
  data,
  reactedTabs
});
