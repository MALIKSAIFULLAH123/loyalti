/**
 * @type: route
 * name: friend.home
 * path: /friend
 * chunkName: pages.friend
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'friend',
  pageName: 'friend.home',
  loginRequired: true,
  resourceName: 'friend'
});
