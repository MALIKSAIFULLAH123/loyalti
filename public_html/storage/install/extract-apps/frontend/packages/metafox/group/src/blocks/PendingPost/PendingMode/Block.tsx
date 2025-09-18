/**
 * @type: block
 * name: group.manage.pendingMode
 * title: Group - Manager - Pending Mode
 * keywords: group
 * description: Manager Pending Mode
 * thumbnail:
 * experiment: true
 */
import {
  connectItemView,
  createBlock,
  connectSubject
} from '@metafox/framework';
import Base from './Base';

const Enhancer = connectSubject(connectItemView(Base, () => {}));

export default createBlock<any>({
  extendBlock: Enhancer,
  overrides: {
    title: 'pending_mode',
    blockLayout: 'App List Pending Posts',
    contentType: 'group',
    showWhen: ['truthy', 'profile.extra.can_manage_pending_mode']
  }
});
