/**
 * @type: reducer
 * name: notification
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';

const appName = 'notification';

export default combineReducers({
  entities: createEntityReducer('notification'),
  uiConfig: createUIReducer(appName, uiConfig)
});
