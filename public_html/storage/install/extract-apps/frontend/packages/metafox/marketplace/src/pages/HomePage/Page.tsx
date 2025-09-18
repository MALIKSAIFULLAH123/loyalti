/**
 * @type: route
 * name: marketplace.home
 * path: /marketplace
 * chunkName: pages.marketplace
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'marketplace',
  pageName: 'marketplace.home',
  resourceName: 'marketplace'
});
