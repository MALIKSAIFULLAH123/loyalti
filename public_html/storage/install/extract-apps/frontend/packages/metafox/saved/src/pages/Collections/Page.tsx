/**
 * @type: route
 * name: saved.list
 * path: /saved/list/:collection_id(\d+), /saved/list/:collection_id(\d+)/:slug?
 * chunkName: pages.saved
 * bundle: web
 */

import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'saved',
  resourceName: 'saved',
  pageName: 'saved.list',
  pageType: 'browseItem'
});
