/**
 * @type: route
 * name: user.search
 * path: /user/search
 * chunkName: pages.user
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'user',
  resourceName: 'user',
  pageName: 'user.search'
});
