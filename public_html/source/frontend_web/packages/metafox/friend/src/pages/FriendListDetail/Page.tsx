/**
 * @type: route
 * name: friend.listDetail
 * path: /friend/list/:list_id(\d+)/:slug?
 * chunkName: pages.friend
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'friend',
  resourceName: 'friend',
  pageName: 'friend.listDetail',
  headingMeta: true,
  breadcrumb: false,
  backPage: false,
  loginRequired: true
});
