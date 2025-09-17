/**
 * @type: route
 * name: friend.welcomeRequests
 * path: /friend/welcome
 * chunkName: pages.friend
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'friend',
  pageName: 'friend.welcomeRequests',
  resourceName: 'friend_request',
  loginRequired: true,
  defaultTab: 'incoming'
});
