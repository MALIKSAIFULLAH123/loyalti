/**
 * @type: route
 * name: advertise.edit
 * path: /advertise/edit/:id,/advertise/add
 * chunkName: pages.advertise
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'advertise',
  resourceName: 'advertise',
  pageName: 'advertise.edit'
});
