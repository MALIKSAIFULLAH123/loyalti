/**
 * @type: reducer
 * name: ewallet
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import uiConfig from './uiConfig';
import paymentSettings from './payment';
import { APP_EWALLET } from '../constants';

const appName = APP_EWALLET;

export default combineReducers({
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, uiConfig),
  paymentSettings
});
