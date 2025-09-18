/**
 * @type: route
 * name: video.browse
 * path: /video/:tab(all|friend|pending|my-pending)
 * chunkName: pages.video
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'video',
  resourceName: 'video',
  pageName: 'video.browse',
  categoryName: 'video_category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
