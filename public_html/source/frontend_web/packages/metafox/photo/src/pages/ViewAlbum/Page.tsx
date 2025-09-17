/**
 * @type: route
 * name: photo_album.view
 * path: photo/album/:id(\d+)/:slug?
 * chunkName: pages.photo
 * bundle: web
 */

import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'photo',
  resourceName: 'photo_album',
  pageName: 'photo_album.view'
});
