/**
 * @type: route
 * name: friends.birthday
 * path: /friend/birthday
 * chunkName: pages.friend
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'friend',
  pageName: 'friend.birthday',
  resourceName: 'friend',
  defaultTab: 'birthday',
  loginRequired: true
});
