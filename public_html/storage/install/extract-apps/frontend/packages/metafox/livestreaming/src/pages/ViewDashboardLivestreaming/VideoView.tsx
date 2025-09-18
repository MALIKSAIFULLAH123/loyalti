/**
 * @type: route
 * name: livestreaming.dashboard
 * path: /live-video/dashboard/:id
 * chunkName: pages.livestreaming
 * bundle: web
 */

import createPageLiveVideoDashboard from './createPageLiveVideoDashboard';

export default createPageLiveVideoDashboard({
  appName: 'livestreaming',
  pageName: 'livestreaming.dashboard',
  resourceName: 'live_video'
});
