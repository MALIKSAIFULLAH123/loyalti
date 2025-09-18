/**
 * @type: route
 * name: marketplace_invoice.sold
 * path: /marketplace/invoice-sold
 * chunkName: pages.marketplace
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'marketplace',
  pageName: 'marketplace_invoice.sold',
  resourceName: 'marketplace_invoice',
  loginRequired: true,
  defaultTab: 'sold_invoice',
  paramCreator: prev => {
    let view = prev.tab?.replace(/-/g, '_');

    if (view === 'sold_invoice') {
      view = 'sold';
    }

    return {
      view
    };
  }
});
