/**
 * @type: route
 * name: sevent.edit
 * path: /sevent/edit/:id,/sevent/add
 * chunkName: pages.sevent
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'sevent',
  resourceName: 'sevent',
  pageName: 'sevent.edit'
});
