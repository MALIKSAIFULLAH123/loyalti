/**
 * @type: route
 * name: livestreaming.home
 * path: /live-video
 * chunkName: pages.livestreaming
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'livestreaming',
  pageName: 'livestreaming.home',
  resourceName: 'live_video'
});
