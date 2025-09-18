/**
 * @type: route
 * name: video.home
 * path: /video
 * chunkName: pages.video
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'video',
  pageName: 'video.home',
  resourceName: 'video'
});
