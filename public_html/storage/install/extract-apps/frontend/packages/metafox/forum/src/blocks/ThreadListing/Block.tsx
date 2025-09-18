/**
 * @type: block
 * name: forum.block.threadListingBlock
 * title: Threads
 * keywords: forum
 * description: Display listing thread
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  defaults: {
    title: 'Threads',
    itemView: 'forum_thread.itemView.mainCard'
  }
});
