/**
 * @type: block
 * name: group.manage.pendingPostsList
 * title: Group - Manager - Pending Posts List
 * keywords: group
 * description: Manager Pending Posts List
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';

export default createBlock<any>({
  name: 'GroupPendingPosts',
  extendBlock: 'core.block.listview',
  defaults: {
    moduleName: 'group',
    resourceName: 'group',
    actionName: 'viewPendingPost',
    title: 'pending_posts',
    itemView: 'group.itemView.pendingPost',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - PendingPost - Main Card',
    itemLayout: 'Group - PendingPost - Main Card',
    emptyPage: 'core.block.no_content_with_icon',
    contentType: 'group',
    canLoadMore: true,
    emptyPageProps: {
      title: 'no_pending_post',
      description: 'there_are_no_pending_post',
      image: 'ico-file-text-o',
      noBlock: true
    }
  }
});
