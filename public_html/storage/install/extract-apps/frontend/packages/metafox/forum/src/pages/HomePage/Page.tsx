/**
 * @type: route
 * name: forum.home
 * path: /forum
 * chunkName: pages.forum
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'forum',
  pageName: 'forum.home',
  resourceName: 'forum'
});
