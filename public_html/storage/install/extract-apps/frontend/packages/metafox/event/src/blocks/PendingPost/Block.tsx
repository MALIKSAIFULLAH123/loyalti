/**
 * @type: block
 * name: event.block.pendingPosts
 * keyword: event, pending posts
 * title: Pending Post
 */

import { BlockViewProps, createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<BlockViewProps>({
  extendBlock: Base,
  name: 'EventPendingPosts'
});
