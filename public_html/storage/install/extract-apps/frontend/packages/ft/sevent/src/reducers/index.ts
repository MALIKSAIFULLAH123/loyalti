/**
 * @type: reducer
 * name: sevent
 */

import {
  combineReducers,
  createEntityReducer,
  createUIReducer
} from '@metafox/framework';
import { APP_NAME } from '../constants';
import seventActive from './active';

export default combineReducers({
  entities: createEntityReducer(APP_NAME),
  uiConfig: createUIReducer(APP_NAME, {
    sidebarHeader: {
      homepageHeader: {
        title: 'sevents',
        to: '/sevent',
        icon: 'ico-newspaper-o'
      }
    },
    sidebarCategory: {
      dataSource: { apiUrl: '/sevent-category' },
      href: '/sevent/category',
      title: 'Genres'
    },
    sidebarSearch: {
      placeholder: 'search_sevents'
    },
    menus: {}
  }),
  seventActive
});
