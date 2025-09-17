/**
 * @type: route
 * name: ewallet.transaction
 * path: /ewallet/transaction
 * chunkName: pages.ewallet
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';
import { APP_EWALLET, EWALLET_TRANSACTION } from '../../constants';

export default createLandingPage({
  appName: APP_EWALLET,
  pageName: 'ewallet.transaction',
  resourceName: EWALLET_TRANSACTION,
  defaultTab: 'transaction',
  loginRequired: true
});
