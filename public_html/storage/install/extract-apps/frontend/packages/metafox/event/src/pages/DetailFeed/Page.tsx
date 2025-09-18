/**
 * @type: route
 * name: event.feedDetail
 * path: /event/:id(\d+)/feed/:feed_id(\d+), /event/:id(\d+)/:slug?/feed/:feed_id(\d+)
 * chunkName: pages.event
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'event',
  resourceName: 'event',
  pageName: 'event.feedDetail'
});
