/**
 * @type: route
 * name: advertise.invoice
 * path: /advertise/invoice
 * chunkName: pages.advertise
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'advertise',
  pageName: 'advertise.invoice',
  resourceName: 'advertise_invoice',
  defaultTab: 'invoice',
  loginRequired: true
});
