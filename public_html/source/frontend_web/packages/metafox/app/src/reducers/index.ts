/**
 * @type: reducer
 * name: app
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import { APP_NAME } from '../constants';
import upgrade from './upgrade';

export default combineReducers({
  entities: createEntityReducer(APP_NAME),
  upgrade,
  uiConfig: createUIReducer(APP_NAME, {
    menus: {}
  })
});
