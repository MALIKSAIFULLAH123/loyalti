/**
 * @type: route
 * name: friend.search
 * path: /friend/search
 * chunkName: pages.friend
 * bundle: web
 */
import { createSearchItemPage } from '@metafox/framework';

export default createSearchItemPage({
  appName: 'friend',
  resourceName: 'friend',
  pageName: 'friend.search',
  loginRequired: true
});
