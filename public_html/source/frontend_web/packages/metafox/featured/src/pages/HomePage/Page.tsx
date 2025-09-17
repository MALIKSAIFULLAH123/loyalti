/**
 * @type: route
 * name: featured.home
 * path: /featured
 * chunkName: pages.featured
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'featured',
  pageName: 'featured.home',
  resourceName: 'featured_item',
  loginRequired: true
});
