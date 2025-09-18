/**
 * @type: route
 * name: event.hosted
 * path: /event/hosted
 * chunkName: pages.event
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'event',
  pageName: 'event.hosted',
  resourceName: 'event',
  defaultTab: 'hosted',
  loginRequired: true
});
