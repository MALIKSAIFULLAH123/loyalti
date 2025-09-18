/**
 * @type: block
 * name: group.manage.myPendingPosts
 * title: Manager Pending Posts
 * keywords: group
 * description: Manager Pending Posts
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<any>({
  name: 'GroupMyPendingPosts',
  extendBlock: Base,
  overrides: {
    title: 'pending',
    itemView: 'group.itemView.myPendingPost',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - PendingPost - Main Card',
    itemLayout: 'Group - PendingPost - Main Card',
    contentType: 'group'
  }
});
