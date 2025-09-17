/**
 * @type: route
 * name: friend.sendRequests
 * path: /friend/send-requests
 * chunkName: pages.friend
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'friend',
  pageName: 'friend.sendRequests',
  resourceName: 'friend_request',
  defaultTab: 'send_request',
  loginRequired: true
});
