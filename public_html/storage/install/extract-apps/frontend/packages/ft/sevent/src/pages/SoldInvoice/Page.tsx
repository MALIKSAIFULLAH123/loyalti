/**
 * @type: route
 * name: sevent_invoice.sold
 * path: /sevent/invoice-sold
 * chunkName: pages.sevent
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'sevent',
  pageName: 'sevent_invoice.sold',
  resourceName: 'sevent_invoice',
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
