/**
 * @type: route
 * name: forum.view
 * path: /forum/:id(\d+)/:slug?
 * chunkName: pages.forum
 * bundle: web
 */

import { createViewItemPage } from '@metafox/framework';

const APP_NAME = 'forum';
const RESOURCE_NAME = 'forum';

export default createViewItemPage({
  appName: APP_NAME,
  pageName: 'forum.view',
  resourceName: RESOURCE_NAME
});
