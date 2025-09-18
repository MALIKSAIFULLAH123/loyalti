/**
 * @type: route
 * name: marketplace_invoice.view
 * path: /marketplace/invoice/:id(\d+)/:slug?
 * chunkName: pages.marketplace
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'marketplace',
  resourceName: 'marketplace_invoice',
  pageName: 'marketplace_invoice.view',
  loginRequired: true
});
