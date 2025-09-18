/**
 * @type: route
 * name: poll.home
 * path: /poll
 * chunkName: pages.poll
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'poll',
  pageName: 'poll.home',
  resourceName: 'poll'
});
