/**
 * @type: route
 * name: photo.browse
 * path: /photo/:tab(all|friend|pending|my-pending)
 * chunkName: pages.photo
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'photo',
  pageName: 'photo.browse',
  resourceName: 'photo',
  categoryName: 'photo_category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
