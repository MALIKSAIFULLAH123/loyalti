/**
 * @type: reducer
 * name: quiz
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';

const appName = 'quiz';

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig)
});
