/**
 * @type: reducer
 * name: featured
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
        title: 'featured',
        to: '/featured',
        icon: 'ico-diamond'
      }
    }
  })
});
