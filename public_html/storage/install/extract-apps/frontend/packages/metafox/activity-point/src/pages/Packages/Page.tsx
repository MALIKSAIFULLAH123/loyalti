/**
 * @type: route
 * name: activity_point.packages
 * path: /activitypoint, /activitypoint/packages
 * chunkName: pages.activitypoint
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'activitypoint',
  pageName: 'activity_point.packages',
  resourceName: 'activitypoint_package',
  defaultTab: 'packages',
  loginRequired: true
});
