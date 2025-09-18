/**
 * @type: route
 * name: groups.my
 * path: /group/my
 * chunkName: pages.group
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'group',
  pageName: 'group.my',
  resourceName: 'group',
  defaultTab: 'my',
  loginRequired: true
});
