/**
 * @type: route
 * name: user.profile
 * path: /user/:id(\d+)/:tab?, /user/:user_id/photo\?stab=albums&album_id=:id
 * bundle: web
 */

import { createProfilePage } from '@metafox/framework';

export default createProfilePage({
  appName: 'user',
  resourceName: 'user',
  pageName: 'user.profile'
});
