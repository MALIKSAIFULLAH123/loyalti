/**
 * @type: route
 * name: video.edit
 * path: /video/edit/:id
 * chunkName: pages.video
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'video',
  resourceName: 'video',
  pageName: 'video.edit'
});
