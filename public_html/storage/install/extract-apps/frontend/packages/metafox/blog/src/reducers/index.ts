/**
 * @type: reducer
 * name: blog
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
        title: 'blogs',
        to: '/blog',
        icon: 'ico-newspaper-alt'
      }
    },
    sidebarCategory: {
      dataSource: { apiUrl: '/blog-category' },
      href: '/blog/category',
      title: 'categories'
    },
    sidebarSearch: {
      placeholder: 'search_blogs'
    },
    menus: {}
  })
});
