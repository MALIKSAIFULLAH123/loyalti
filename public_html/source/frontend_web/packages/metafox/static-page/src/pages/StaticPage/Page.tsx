/**
 * @type: route
 * name: static.page
 * path: /static-page/:id
 * chunkName: pages.static
 * bundle: web
 */

import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'static-page',
  resourceName: 'static_page',
  pageName: 'static.page'
});
