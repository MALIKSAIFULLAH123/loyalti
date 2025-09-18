/**
 * @type: route
 * name: sevent_invoice.bought
 * path: /sevent/invoice-bought
 * chunkName: pages.sevent
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'sevent',
  pageName: 'sevent_invoice.bought',
  resourceName: 'sevent_invoice',
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
