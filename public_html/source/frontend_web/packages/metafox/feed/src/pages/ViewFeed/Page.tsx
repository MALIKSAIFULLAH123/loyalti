/**
 * @type: route
 * name: feed.view
 * path: /feed/:id(\d+), /activity_post/:id(\d+)
 * chunkName: pages.feed
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'feed',
  resourceName: 'feed',
  pageName: 'feed.view'
});
