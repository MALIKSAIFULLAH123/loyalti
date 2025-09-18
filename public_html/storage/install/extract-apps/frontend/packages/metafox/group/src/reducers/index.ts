/**
 * @type: reducer
 * name: group
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import { APP_GROUP } from '..';
import uiConfig from './uiConfig';

const appName = APP_GROUP;

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig)
});
