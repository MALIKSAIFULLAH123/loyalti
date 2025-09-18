/**
 * @type: block
 * name: group.manage.pendingPosts
 * title: Manager Pending Posts
 * keywords: group
 * description: Manager Pending Posts
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
  name: 'GroupManagePendingPosts',
  extendBlock: Enhancer,
  overrides: {
    title: 'Pending',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - PendingPost - Main Card',
    contentType: 'group'
  }
});
