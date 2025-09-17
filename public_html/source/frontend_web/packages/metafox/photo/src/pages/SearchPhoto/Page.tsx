/**
 * @type: route
 * name: photo.search
 * path: /photo/search, /photo/category/:category_id/:slug?
 * chunkName: pages.photo
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'photo',
  pageName: 'photo.search',
  resourceName: 'photo',
  categoryName: 'photo_category'
});
