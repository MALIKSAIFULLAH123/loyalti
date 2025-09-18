/**
 * @type: route
 * name: livestreaming.search
 * path: /live-video/search
 * chunkName: pages.livestream
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'livestreaming',
  pageName: 'livestreaming.search',
  resourceName: 'live_video'
});
