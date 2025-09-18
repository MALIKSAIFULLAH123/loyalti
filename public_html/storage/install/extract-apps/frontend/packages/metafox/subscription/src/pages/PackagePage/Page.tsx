/**
 * @type: route
 * name: subscription.package
 * path: /subscription/package
 * chunkName: pages.subscription
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'subscription',
  pageName: 'subscription.package',
  resourceName: 'subscription_package',
  defaultTab: 'package'
});
