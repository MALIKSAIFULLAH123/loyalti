/**
 * @type: route
 * name: pages.browse
 * path: /pages/:tab(all|friend|invited|liked|my-pending|pending|my), /page/:tab(all|friend|invited|liked|my-pending|pending|my)
 * chunkName: pages.page
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'page',
  resourceName: 'page',
  pageName: 'pages.browse',
  categoryName: 'page-category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
