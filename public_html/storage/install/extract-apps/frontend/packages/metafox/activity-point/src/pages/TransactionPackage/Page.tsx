/**
 * @type: route
 * name: activity_point.transactions_package
 * path: /activitypoint/transactions-package
 * chunkName: pages.activitypoint
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'activitypoint',
  pageName: 'activity_point.transactions_package',
  resourceName: 'package_transaction',
  defaultTab: 'package_transactions',
  loginRequired: true
});
