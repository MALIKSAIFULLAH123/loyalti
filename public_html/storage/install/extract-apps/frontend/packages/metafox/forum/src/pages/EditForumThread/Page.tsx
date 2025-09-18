/**
 * @type: route
 * name: forum_thread.edit
 * path: /forum/thread/edit/:id/:slug?, /forum/thread/add, /forum/thread/add/:slug?
 * chunkName: pages.forum
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'forum',
  resourceName: 'forum_thread',
  pageName: 'forum_thread.edit'
});
