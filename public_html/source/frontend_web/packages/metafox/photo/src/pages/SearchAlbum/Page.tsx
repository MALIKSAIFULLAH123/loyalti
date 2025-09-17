/**
 * @type: route
 * name: photo_album.search
 * path: /photo/albums/search, /photo/albums/category/:category/:slug?
 * chunkName: pages.photo
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'photo',
  pageName: 'photo_album.search',
  resourceName: 'photo_album',
  categoryName: 'photo_category',
  paramCreator: prev => ({ tab: `${prev.tab}_albums` })
});
