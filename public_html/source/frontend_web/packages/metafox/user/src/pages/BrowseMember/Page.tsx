/**
 * @type: route
 * name: user.browse
 * path: /user/:tab(recommend|recent|featured)
 * chunkName: pages.user
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'user',
  resourceName: 'user',
  pageName: 'user.browse',
  paramCreator: prev => ({
    sort: prev.tab === 'active' ? 'active' : undefined
  })
});
