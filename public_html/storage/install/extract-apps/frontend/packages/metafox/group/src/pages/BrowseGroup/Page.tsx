/**
 * @type: route
 * name: groups.browse
 * path: /group/:tab(all|friend|invited|joined|pending|my-pending)
 * chunkName: pages.group
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'groups.browse',
  categoryName: 'group_category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
