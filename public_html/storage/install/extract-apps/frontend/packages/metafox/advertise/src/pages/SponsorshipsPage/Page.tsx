/**
 * @type: route
 * name: advertise.sponsorship
 * path: /advertise/sponsor
 * chunkName: pages.advertise
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'advertise',
  pageName: 'advertise.sponsorship',
  resourceName: 'advertise_sponsor',
  defaultTab: 'sponsorship',
  loginRequired: true
});
