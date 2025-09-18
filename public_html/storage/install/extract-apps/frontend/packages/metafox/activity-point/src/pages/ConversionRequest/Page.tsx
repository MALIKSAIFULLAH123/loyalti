/**
 * @type: route
 * name: activity_point.conversion_request
 * path: /activitypoint/conversion-request
 * chunkName: pages.activitypoint
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'activitypoint',
  pageName: 'activity_point.conversion_request',
  resourceName: 'activitypoint_conversion_request',
  defaultTab: 'conversion_request',
  loginRequired: true
});
