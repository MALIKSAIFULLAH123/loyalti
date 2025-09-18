/**
 * @type: reducer
 * name: forum
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';

const appName = 'forum';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig)
});
