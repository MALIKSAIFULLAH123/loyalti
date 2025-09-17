/**
 * @type: route
 * name: ewallet.home
 * path: /ewallet
 * chunkName: pages.ewallet
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';
import { APP_EWALLET } from '../../constants';

export default createLandingPage({
  appName: APP_EWALLET,
  pageName: 'ewallet.home',
  resourceName: APP_EWALLET,
  loginRequired: true
});
