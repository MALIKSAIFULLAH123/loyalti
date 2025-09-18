/**
 * @type: route
 * name: video.search
 * path: /video/search, /video/category/:category_id/:slug?
 * chunkName: pages.video
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'video',
  pageName: 'video.search',
  resourceName: 'video',
  categoryName: 'video_category'
});
