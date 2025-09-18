/**
 * @type: route
 * name: subscription.home
 * path: /subscription
 * chunkName: pages.subscription
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'subscription',
  pageName: 'subscription.home',
  resourceName: 'subscription_invoice',
  loginRequired: true,
  paramCreator: prev => ({
    heading: 'subscriptions'
  })
});
