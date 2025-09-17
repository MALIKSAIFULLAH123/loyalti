/**
 * @type: route
 * name: user.edit_profile
 * path: /user/:id(\d+)/profile
 * chunkName: pages.user
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'user',
  resourceName: 'user',
  pageName: 'user.edit_profile',
  successAction: 'user/updateProfileInfo'
});
