/**
 * @type: route
 * name: invite.home
 * path: /invite
 * chunkName: invite.page
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'invite',
  resourceName: 'invite',
  pageName: 'invite.home',
  loginRequired: true
});
