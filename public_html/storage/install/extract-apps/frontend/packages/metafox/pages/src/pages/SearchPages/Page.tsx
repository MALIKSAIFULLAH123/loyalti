/**
 * @type: route
 * name: pages.search
 * path: /pages/search, /page/type/:type_id/:name?, /page/category/:category_id/:name?, /page/search
 * chunkName: pages.page
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'page',
  resourceName: 'page',
  pageName: 'pages.search',
  categoryName: 'type,category'
});
