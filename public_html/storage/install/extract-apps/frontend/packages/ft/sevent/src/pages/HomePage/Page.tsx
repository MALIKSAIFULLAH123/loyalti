/**
 * @type: route
 * name: sevent.home
 * path: /sevent
 * chunkName: pages.sevent
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'sevent',
  pageName: 'sevent.home',
  resourceName: 'sevent'
});
