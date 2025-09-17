/**
 * @type: route
 * name: photo_album.edit
 * path: /photo/album/edit/:id, /photo/album/add
 * chunkName: pages.photo
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'photo',
  resourceName: 'photo_album',
  pageName: 'photo_album.edit'
});
