/**
 * @type: route
 * name: video.share
 * path: /video/share
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'video',
  resourceName: 'video',
  pageName: 'video.share'
});
