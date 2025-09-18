/**
 * @type: route
 * name: groups.manageMyContent
 * path: /group/:id/review_my_content/:tab?
 * chunkName: pages.group
 * bundle: web
 */
import { createMultiTabPage } from '@metafox/framework';

export default createMultiTabPage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'groups.manageMyContent',
  defaultTab: 'published',
  defaultTabKey: 'defaultActiveContentTab',
  tabs: {
    pending: 'group.manage.myPendingPosts',
    published: 'group.manage.myPublishedPosts',
    declined: 'group.manage.myDeclinedPosts',
    removed: 'group.manage.myRemovedPosts'
  },
  loginRequired: true,
  heading: 'your_content'
});
