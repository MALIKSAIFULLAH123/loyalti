/**
 * @type: route
 * name: event.past
 * path: /event/past
 * chunkName: pages.event
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'event',
  pageName: 'event.past',
  resourceName: 'event',
  defaultTab: 'past',
  loginRequired: true
});
