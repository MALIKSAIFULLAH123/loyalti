/**
 * @type: route
 * name: photo.edit
 * path: /photo/edit/:id(\d+), /photo/add
 * chunkName: pages.photo
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'photo',
  resourceName: 'photo',
  pageName: 'photo.edit'
});
