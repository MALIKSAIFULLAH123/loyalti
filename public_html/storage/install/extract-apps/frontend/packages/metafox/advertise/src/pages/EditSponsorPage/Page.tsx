/**
 * @type: route
 * name: advertise.sponsor.edit
 * path: /advertise/sponsor/edit/:id,advertise/sponsor/add
 * chunkName: pages.advertise
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'advertise',
  resourceName: 'advertise_sponsor',
  pageName: 'advertise.sponsor.edit'
});
