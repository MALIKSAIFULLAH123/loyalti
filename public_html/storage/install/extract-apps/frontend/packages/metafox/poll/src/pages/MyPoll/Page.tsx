/**
 * @type: route
 * name: poll.my
 * path: /poll/my
 * chunkName: pages.poll
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'poll',
  pageName: 'poll.my',
  resourceName: 'poll',
  defaultTab: 'my',
  loginRequired: true
});
