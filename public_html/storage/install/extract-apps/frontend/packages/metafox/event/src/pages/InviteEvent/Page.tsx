/**
 * @type: route
 * name: event.invites
 * path: /event/invites
 * chunkName: pages.event
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'event',
  pageName: 'event.invites',
  resourceName: 'event',
  defaultTab: 'invites',
  loginRequired: true
});
