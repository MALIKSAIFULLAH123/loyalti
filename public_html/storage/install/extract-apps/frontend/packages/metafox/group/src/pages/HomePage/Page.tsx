/**
 * @type: route
 * name: groups.home
 * path: /group
 * chunkName: pages.group
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'group',
  pageName: 'groups.home',
  resourceName: 'group'
});
