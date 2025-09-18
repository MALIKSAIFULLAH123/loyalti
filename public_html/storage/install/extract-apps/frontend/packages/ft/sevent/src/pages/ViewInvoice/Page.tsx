/**
 * @type: route
 * name: sevent_invoice.view
 * path: /sevent/invoice/:id(\d+)/:slug?
 * chunkName: pages.sevent
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'sevent',
  resourceName: 'sevent_invoice',
  pageName: 'sevent_invoice.view',
  loginRequired: true
});
