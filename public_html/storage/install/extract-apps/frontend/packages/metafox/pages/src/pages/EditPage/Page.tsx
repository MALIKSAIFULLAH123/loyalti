/**
 * @type: route
 * name: pages.edit
 * path: /pages/edit/:id?, /pages/add, /page/edit/:id?, /page/add
 * chunkName: pages.page
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'page',
  resourceName: 'page',
  pageName: 'pages.edit',
  disableFormOnSuccess: true
});
