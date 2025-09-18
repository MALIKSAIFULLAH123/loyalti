/**
 * @type: block
 * name: group.manage.membershipQuestionList
 * title: Group - Manage - MembershipQuestion List
 * keywords: group
 * description: Setting Question Membership List
 * thumbnail:
 * experiment: true
 */
import { createBlock } from '@metafox/framework';

export default createBlock<any>({
  extendBlock: 'core.block.listview',
  overrides: {
    itemView: 'group.itemView.membershipQuestionCard',
    headerActions: [
      {
        as: 'group.addNewQuestionButton',
        enabledWhen: ['truthy', 'item.extra.can_add_membership_question']
      }
    ],
    showWhen: ['truthy', 'profile.extra.can_manage_membership_question']
  },
  defaults: {
    title: 'set_up_membership_questions',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - MemberQuestion - Main Card',
    itemLayout: 'Group - MemberQuestion - Main Card',
    itemView: 'group.itemView.membershipQuestionCard',
    emptyPage: 'core.block.no_content_with_icon',
    gridVariant: 'listView',
    moduleName: 'group',
    resourceName: 'group_question',
    actionName: 'viewAll',
    canLoadMore: true,
    emptyPageProps: {
      title: 'membership_questions',
      description: 'ask_pending_members_question_when_they_request',
      image: 'ico-note-paper-o'
    }
  }
});
