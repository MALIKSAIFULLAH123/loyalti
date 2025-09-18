/**
 * @type: route
 * name: pages.home
 * path: /pages, /page
 * chunkName: pages.page
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'page',
  pageName: 'pages.home',
  resourceName: 'page'
});
