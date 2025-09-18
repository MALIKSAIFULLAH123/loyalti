/**
 * @type: block
 * name: group.manage.moderationRights
 * title: Group - Manage - Moderation Rights
 * keywords: group
 * description: Manager Moderation Rights
 * thumbnail:
 * experiment: true
 */
import { connectItemView, createBlock } from '@metafox/framework';
import Base from './Base';

const Enhancer = connectItemView(Base, () => {});

export default createBlock<any>({
  extendBlock: Enhancer,
  overrides: {
    contentType: 'group',
    title: 'moderation_rights',
    showWhen: ['truthy', 'profile.extra.can_manage_setting'],
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - PendingPost - Main Card',
    itemLayout: 'Group - PendingPost - Main Card'
  }
});
