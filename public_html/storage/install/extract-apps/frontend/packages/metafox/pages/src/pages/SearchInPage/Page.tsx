/**
 * @type: route
 * name: page.search
 * path: /page/search/:id/\?q=:q, /page/search/:id/:slug?
 * chunkName: pages.page
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'page',
  resourceName: 'page',
  pageName: 'page.search',
  viewResource: 'viewSearchInPage'
});
