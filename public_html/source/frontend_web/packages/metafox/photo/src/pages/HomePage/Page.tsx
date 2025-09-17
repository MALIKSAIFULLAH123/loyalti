/**
 * @type: route
 * name: photo.home
 * path: /photo
 * chunkName: pages.photo
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'photo',
  pageName: 'photo.home',
  resourceName: 'photo'
});
