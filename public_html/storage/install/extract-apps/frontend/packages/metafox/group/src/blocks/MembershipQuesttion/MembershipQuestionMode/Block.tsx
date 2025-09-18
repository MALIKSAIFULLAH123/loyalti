/**
 * @type: block
 * name: group.manage.membershipQuestionMode
 * title: Group - Manage - MembershipQuestion Mode
 * keywords: group
 * description: Manager MembershipQuestion Mode
 * thumbnail:
 * experiment: true
 */
import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base from './Base';

const Enhancer = connectSubject(connectItemView(Base, () => {}));

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - Rule - Main Card',
    itemLayout: 'Group - Rule - Main Card',
    contentType: 'group',
    title: 'membership_questions',
    showWhen: ['truthy', 'profile.extra.can_manage_membership_question']
  }
});
