/**
 * @type: route
 * name: video.my
 * path: /video/my
 * chunkName: pages.video
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'video',
  pageName: 'video.my',
  resourceName: 'video',
  defaultTab: 'my',
  loginRequired: true
});
