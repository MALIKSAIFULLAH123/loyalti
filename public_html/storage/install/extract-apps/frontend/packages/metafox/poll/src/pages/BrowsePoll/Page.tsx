/**
 * @type: route
 * name: poll.browse
 * path: /poll/:tab(all|friend|pending|feature|sponsor|my-pending)
 * chunkName: pages.poll
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'poll',
  resourceName: 'poll',
  pageName: 'poll.browse',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
