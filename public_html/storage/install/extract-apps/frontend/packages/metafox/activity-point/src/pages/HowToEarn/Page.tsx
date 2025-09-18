/**
 * @type: route
 * name: activity_point.how_to_earn
 * path: /activitypoint/how-to-earn
 * chunkName: pages.activitypoint
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'activitypoint',
  pageName: 'activity_point.how_to_earn',
  resourceName: 'activitypoint',
  defaultTab: 'how-to-earn',
  loginRequired: true
});
