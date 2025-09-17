/**
 * @type: route
 * name: photo.my
 * path: /photo/my
 * chunkName: pages.photo
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'photo',
  pageName: 'photo.my',
  resourceName: 'photo',
  defaultTab: 'my',
  loginRequired: true
});
