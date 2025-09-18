/**
 * @type: route
 * name: page.feedDetail
 * path: /page/:id(\d+)/feed/:feed_id(\d+)
 * chunkName: pages.page
 * bundle: web
 */
import { createProfilePage } from '@metafox/framework';

export default createProfilePage({
  appName: 'page',
  pageName: 'page.feedDetail',
  resourceName: 'page',
  paramCreator: prev => ({
    tab: 'home'
  })
});
