/**
 * @type: route
 * name: marketplace.search_map
 * path: /marketplace/search-map
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'marketplace',
  resourceName: 'marketplace',
  pageName: 'marketplace.search_map',
  viewResource: 'viewItemsOnMap'
});
