/**
 * @type: route
 * name: ewallet.request
 * path: /ewallet/request
 * chunkName: pages.ewallet
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';
import { APP_EWALLET, EWALLET_WITHDRAW_REQUEST } from '../../constants';

export default createLandingPage({
  appName: APP_EWALLET,
  pageName: 'ewallet.request',
  resourceName: EWALLET_WITHDRAW_REQUEST,
  defaultTab: 'request',
  loginRequired: true
});
