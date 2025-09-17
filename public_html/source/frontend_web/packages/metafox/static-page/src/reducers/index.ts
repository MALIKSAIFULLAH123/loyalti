/**
 * @type: reducer
 * name: static-page
 */
import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import { APP_NAME as appName } from '../constants';
import staticOptions from './staticOptions';

export default combineReducers({
  staticOptions,
  entities: createEntityReducer(appName),
  uiConfig: createUIReducer(appName, {})
});
