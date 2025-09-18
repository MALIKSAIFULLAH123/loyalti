/**
 * @type: route
 * name: sevent.view
 * path: /sevent/:id(\d+)/:slug?
 * chunkName: pages.sevent
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'sevent',
  resourceName: 'sevent',
  pageName: 'sevent.view'
});
