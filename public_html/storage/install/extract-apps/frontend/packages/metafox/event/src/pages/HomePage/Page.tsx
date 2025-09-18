/**
 * @type: route
 * name: event.home
 * path: /event
 * chunkName: pages.event
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'event',
  pageName: 'event.home',
  resourceName: 'event'
});
