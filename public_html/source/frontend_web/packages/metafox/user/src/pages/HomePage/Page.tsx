/**
 * @type: route
 * name: user.home
 * path: /user
 * chunkName: pages.user
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'user',
  pageName: 'user.home',
  resourceName: 'user'
});
