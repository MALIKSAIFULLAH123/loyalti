/**
 * @type: block
 * name: group.manage.myPublishedPosts
 * title: Manager Published Posts
 * keywords: group
 * description: Manager Published Posts
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<any>({
  name: 'MyGroupPublishedPosts',
  extendBlock: Base,
  overrides: {
    title: 'published',
    itemView: 'group.itemView.myPublishedPost',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - PendingPost - Main Card',
    itemLayout: 'Group - PendingPost - Main Card',
    contentType: 'group'
  }
});
