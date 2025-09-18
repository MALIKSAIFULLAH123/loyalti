/**
 * @type: route
 * name: invite.manage
 * path: /invite/manage
 * chunkName: invite.page
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  defaultTab: 'manage',
  appName: 'invite',
  resourceName: 'invite',
  pageName: 'invite.manage',
  loginRequired: true
});
