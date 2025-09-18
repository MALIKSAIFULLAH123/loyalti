/**
 * @type: block
 * name: group.manage.membershipQuestion
 * title: Group - Manage - MembershipQuestion
 * keywords: group
 * description: Group - Manage - MembershipQuestion
 * thumbnail:
 * experiment: true
 */

import {
  connectItemView,
  connectSubject,
  createBlock,
  ListViewBlockProps
} from '@metafox/framework';
import Base from './Base';

const Enhancer = connectSubject(connectItemView(Base, () => {}));

export default createBlock<ListViewBlockProps>({
  name: 'MembershipQuestion',
  extendBlock: Enhancer,
  overrides: {
    emptyPage: 'hide',
    authRequired: true,
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - PendingPost - Main Card',
    contentType: 'group'
  }
});
