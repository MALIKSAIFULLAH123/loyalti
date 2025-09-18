/**
 * @type: route
 * name: event.friend
 * path: /event/friend
 * chunkName: pages.event
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'event',
  pageName: 'event.friend',
  resourceName: 'event',
  defaultTab: 'friend',
  loginRequired: true
});
