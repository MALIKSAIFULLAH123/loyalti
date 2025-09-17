/**
 * @type: route
 * name: home.contact
 * path: /contact
 * chunkName: home.contact
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'contact',
  resourceName: 'contact',
  pageName: 'home.contact',
  resourceAction: 'getContactForm',
  loginRequired: false
});
