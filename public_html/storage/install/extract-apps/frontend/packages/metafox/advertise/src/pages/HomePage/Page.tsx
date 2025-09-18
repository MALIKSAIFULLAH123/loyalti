/**
 * @type: route
 * name: advertise.home
 * path: /advertise,/advertise/all
 * chunkName: pages.advertise
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'advertise',
  pageName: 'advertise.home',
  resourceName: 'advertise',
  loginRequired: true,
  paramCreator: prev => ({
    tab: 'my',
    view: 'my'
  })
});
