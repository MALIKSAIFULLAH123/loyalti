/**
 * @type: route
 * name: forum_thread.browse
 * path: /forum/:tab(my|my-pending|subscribed|history|pending|wiki)-thread
 * chunkName: pages.forum
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'forum',
  resourceName: 'forum_thread',
  pageName: 'forum_thread.browse',
  paramCreator: prev => ({
    tab: `${prev.tab?.replace(/-/g, '_')}_thread`,
    view: prev.tab?.replace(/-/g, '_')
  })
});
