/**
 * @type: route
 * name: marketplace.search
 * path: /marketplace/search, /marketplace/category/:category_id(\d+)/:slug?
 * chunkName: pages.marketplace
 * bundle: web
 */

import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'marketplace',
  resourceName: 'marketplace',
  pageName: 'marketplace.search',
  categoryName: 'marketplace_category'
});
