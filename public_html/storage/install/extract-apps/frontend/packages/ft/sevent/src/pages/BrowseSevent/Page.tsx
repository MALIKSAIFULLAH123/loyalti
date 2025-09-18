/**
 * @type: route
 * name: sevent.browse
 * path: /sevent/:tab(my|attending|friend|all|pending|feature|spam|draft|favourite_sevents|my-pending)
 * chunkName: pages.sevent
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'sevent',
  resourceName: 'sevent',
  pageName: 'sevent.browse',
  categoryName: 'sevent_category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
