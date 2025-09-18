/**
 * @type: route
 * name: subscription.view
 * path: /subscription/:id(\d+)/:slug?
 * chunkName: pages.subscription
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'subscription',
  resourceName: 'subscription_invoice',
  pageName: 'subscription_invoice.view',
  loginRequired: true
});
