/**
 * @type: block
 * name: group.manage.membershipAnswerList
 * title: Group - Manage - MembershipQuestion List
 * keywords: group
 * description: Setting Answer Membership List
 * thumbnail:
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<any>({
  extendBlock: Base,
  defaults: {
    title: 'search_requests'
  }
});
