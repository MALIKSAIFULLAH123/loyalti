/**
 * @type: route
 * name: marketplace.view
 * path: /marketplace/:id(\d+), /marketplace/view/:id, /marketplace/:id/:slug
 * chunkName: pages.marketplace
 * bundle: web
 */

import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'marketplace',
  resourceName: 'marketplace',
  pageName: 'marketplace.view'
});
