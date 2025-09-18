/**
 * @type: route
 * name: event.all
 * path: /event/all
 * chunkName: pages.event
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'event',
  pageName: 'event.all',
  resourceName: 'event',
  defaultTab: 'all'
});
