/**
 * @type: route
 * name: notification.home
 * path: /notification
 * chunkName: pages.notification
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'notification',
  pageName: 'notification.home',
  loginRequired: true,
  resourceName: 'notification'
});
