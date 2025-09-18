/**
 * @type: route
 * name: event.search
 * path: /event/search, /event/category/:category_id(\d+)/:slug?
 * chunkName: pages.event
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'event',
  resourceName: 'event',
  pageName: 'event.search',
  categoryName: 'event_category'
});
