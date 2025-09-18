/**
 * @type: route
 * name: marketplace_invoice.bought
 * path: /marketplace/invoice-bought
 * chunkName: pages.marketplace
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'marketplace',
  pageName: 'marketplace_invoice.bought',
  resourceName: 'marketplace_invoice',
  loginRequired: true,
  defaultTab: 'bought_invoice',
  paramCreator: prev => {
    let view = prev.tab?.replace(/-/g, '_');

    if (view === 'bought_invoice') {
      view = 'bought';
    }

    return {
      view
    };
  }
});
