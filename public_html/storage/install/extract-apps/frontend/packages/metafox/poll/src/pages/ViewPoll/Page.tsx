/**
 * @type: route
 * name: poll.view
 * path: /poll/:id(\d+)/:slug?
 * chunkName: pages.poll
 * bundle: web
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'poll',
  resourceName: 'poll',
  pageName: 'poll.view'
});
