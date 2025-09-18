/**
 * @type: route
 * name: announcement.view
 * path: /announcement/:id(\d+)/:slug?
 * chunkName: pages.announcement
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'announcement',
  resourceName: 'announcement',
  pageName: 'announcement.view'
});
