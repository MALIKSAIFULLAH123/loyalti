/**
 * @type: route
 * name: groups.manage
 * path: /group/manage/:id(\d+)/:tab?
 * chunkName: pages.group
 * bundle: web
 */
import { createMultiTabPage } from '@metafox/framework';

export default createMultiTabPage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'groups.manage',
  defaultTab: 'pending_posts',
  defaultTabKey: 'defaultActiveTabManage',
  tabs: {
    membership_questions: 'group.manage.membershipQuestion',
    pending_posts: 'group.manage.pendingPosts',
    group_rules: 'group.manager.groupRule',
    moderation_rights: 'group.manage.moderationRights',
    report: 'group.manager.reportContent',
    membership_requests: 'group.manager.membership_requests',
    membership_invites: 'group.manager.membership_invites'
  },
  showWhen: [
    'or',
    ['truthy', 'item.extra.can_edit'],
    ['truthy', 'item.extra.can_manage_member_report_content'],
    ['truthy', 'item.extra.can_manage_pending_posts']
  ],
  loginRequired: true
});
