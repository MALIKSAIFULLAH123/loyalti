/**
 * @type: route
 * name: livestreaming.browse
 * path: /live-video/:tab(all-streaming|my-streaming|all|friend|my|pending|my-pending)
 * chunkName: pages.livestreaming
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'livestreaming',
  resourceName: 'live_video',
  pageName: 'livestreaming.browse',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
