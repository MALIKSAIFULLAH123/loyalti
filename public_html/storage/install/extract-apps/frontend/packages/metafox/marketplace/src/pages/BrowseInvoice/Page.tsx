/**
 * @type: route
 * name: marketplace_invoice.my
 * path: /marketplace/invoice
 * chunkName: pages.marketplace
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'marketplace',
  pageName: 'marketplace_invoice.my',
  resourceName: 'marketplace_invoice',
  loginRequired: true,
  defaultTab: 'invoice'
});
