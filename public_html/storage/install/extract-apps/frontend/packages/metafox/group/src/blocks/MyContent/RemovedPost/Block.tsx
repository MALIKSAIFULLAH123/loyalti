/**
 * @type: block
 * name: group.manage.myRemovedPosts
 * title: Manager My Removed Posts
 * keywords: group
 * description: Manager My Removed Posts
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<any>({
  name: 'MyGroupRemovedPosts',
  extendBlock: Base,
  overrides: {
    title: 'removed',
    itemView: 'group.itemView.myRemovedPost',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - PendingPost - Main Card',
    itemLayout: 'Group - PendingPost - Main Card',
    contentType: 'group'
  }
});
