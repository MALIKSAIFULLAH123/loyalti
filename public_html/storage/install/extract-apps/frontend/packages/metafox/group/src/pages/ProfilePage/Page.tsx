/**
 * @type: route
 * name: group.profile
 * path: /group/:id(\d+)/:tab?, /group/:user_id/photo\?stab=albums&album_id=:id
 * chunkName: pages.group
 * bundle: web
 */

import { createProfilePage } from '@metafox/framework';

export default createProfilePage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'group.profile',
  loadingTab: 'loading'
});
