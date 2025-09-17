/**
 * @type: route
 * name: photo_album.browse
 * path: /photo/albums/:tab(all|my|friend)? , /photo/:tab(all|my|friend)-albums
 * chunkName: pages.photo
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'photo',
  pageName: 'photo_album.browse',
  resourceName: 'photo_album',
  categoryName: 'photo_category',
  paramCreator: prev => ({ tab: `${prev.tab}_albums` })
});
