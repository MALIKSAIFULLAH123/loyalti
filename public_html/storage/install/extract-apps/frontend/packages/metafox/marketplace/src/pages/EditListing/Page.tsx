/**
 * @type: route
 * name: marketplace.edit
 * path: /marketplace/add, /marketplace/edit/:id, /marketplace/add/:slug?
 * chunkName: pages.marketplace
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'marketplace',
  resourceName: 'marketplace',
  pageName: 'marketplace.edit'
});
