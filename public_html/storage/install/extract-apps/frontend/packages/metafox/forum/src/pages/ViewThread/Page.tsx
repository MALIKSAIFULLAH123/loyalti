/**
 * @type: route
 * name: forum_thread.view
 * path: /forum/thread/:id(\d+)/:slug?
 * chunkName: pages.forum
 * bundle: web
 */

import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'forum',
  resourceName: 'forum_thread',
  pageName: 'forum_thread.view'
});
