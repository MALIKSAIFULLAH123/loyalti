/**
 * @type: route
 * name: marketplace.my
 * path: /marketplace/my
 * chunkName: pages.marketplace
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'marketplace',
  pageName: 'marketplace.my',
  resourceName: 'marketplace',
  defaultTab: 'my',
  loginRequired: true
});
