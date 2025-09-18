/**
 * @type: route
 * name: saved.home
 * path: /saved
 * chunkName: pages.saved
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'saved',
  pageName: 'saved.home',
  loginRequired: true,
  resourceName: 'saved'
});
