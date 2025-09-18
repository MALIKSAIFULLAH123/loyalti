/**
 * @type: route
 * name: groups.search
 * path: /group/search, /group/type/:type_id/:name?, /group/category/:category_id/:slug?
 * chunkName: pages.group
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'groups.search',
  categoryName: 'group_category'
});
