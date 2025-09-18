/**
 * @type: route
 * name: saved.search
 * path: /saved/search
 * chunkName: pages.saved
 */

import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'saved',
  resourceName: 'saved',
  pageName: 'saved.search'
});
