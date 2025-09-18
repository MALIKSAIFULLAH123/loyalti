/**
 * @type: reducer
 * name: advertise
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import { APP_NAME } from '../constants';

export default combineReducers({
  entities: createEntityReducer(APP_NAME),
  uiConfig: createUIReducer(APP_NAME, {
    sidebarHeader: {
      homepageHeader: {
        title: 'advertise',
        to: '/advertise',
        icon: 'ico-newspaper-alt'
      }
    }
  })
});
