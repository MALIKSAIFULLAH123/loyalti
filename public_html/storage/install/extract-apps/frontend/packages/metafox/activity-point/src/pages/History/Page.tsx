/**
 * @type: route
 * name: activity_point.history
 * path: /activitypoint/transactions-history
 * chunkName: pages.activitypoint
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'activitypoint',
  pageName: 'activity_point.history',
  resourceName: 'activitypoint_transaction',
  defaultTab: 'transactions-history',
  loginRequired: true
});
