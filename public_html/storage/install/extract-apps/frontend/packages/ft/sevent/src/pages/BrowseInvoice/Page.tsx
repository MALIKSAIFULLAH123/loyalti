/**
 * @type: route
 * name: sevent_invoice.my
 * path: /sevent/invoice
 * chunkName: pages.sevent
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'sevent',
  pageName: 'sevent_invoice.my',
  resourceName: 'sevent_invoice',
  loginRequired: true,
  defaultTab: 'invoice'
});
