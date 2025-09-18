/**
 * @type: route
 * name: sevent.search_map
 * path: /sevent/search-map
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'sevent',
  resourceName: 'sevent',
  pageName: 'sevent.search_map',
  viewResource: 'viewEventsMap'
});
