/**
 * @type: route
 * name: group.feedDetail
 * path: /group/:id(\d+)/feed/:feed_id(\d+)
 * chunkName: pages.group
 * bundle: web
 */
import { createProfilePage } from '@metafox/framework';

export default createProfilePage({
  appName: 'group',
  pageName: 'group.feedDetail',
  resourceName: 'group',
  paramCreator: prev => ({
    tab: 'home'
  })
});
