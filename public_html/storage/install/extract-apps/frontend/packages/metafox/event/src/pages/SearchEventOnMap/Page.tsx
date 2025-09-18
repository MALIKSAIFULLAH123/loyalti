/**
 * @type: route
 * name: event.search_map
 * path: /event/search-map
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'event',
  resourceName: 'event',
  pageName: 'event.search_map',
  viewResource: 'viewItemsOnMap'
});
