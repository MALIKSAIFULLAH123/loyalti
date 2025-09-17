/**
 * @type: route
 * name: friend.incomingRequests
 * path: /friend/requests
 * chunkName: pages.friend
 * bundle: web
 */

import { createMasterDetailPage } from '@metafox/framework';

export default createMasterDetailPage({
  appName: 'friend',
  pageName: 'friend.incomingRequests',
  pathMap: { from: '/friend/requests', to: '/friend/welcome' },
  loginRequired: true,
  breadcrumb: false,
  pageTitle: 'friend_requests',
  backPage: true,
  backPageProps: {
    title: 'friends',
    to: '/friend'
  }
});
