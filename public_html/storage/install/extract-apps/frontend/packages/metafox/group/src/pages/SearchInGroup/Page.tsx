/**
 * @type: route
 * name: group.search
 * path: /group/search/:id/\?q=:q, /group/search/:id/:slug?
 * chunkName: pages.group
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'group.search',
  viewResource: 'viewSearchInGroup',
  paramCreator: prev => ({
    tab: prev.view
  })
});
