/**
 * @type: route
 * name: advertise.sponsor.feed
 * path: /advertise/sponsor/feed/add
 * chunkName: pages.advertise
 * bundle: web
 */

import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'advertise',
  resourceName: 'advertise_sponsor',
  pageName: 'advertise.sponsor.feed'
});
