/**
 * @type: block
 * name: forum.block.forumListingBlock
 * title: Forums
 * keywords: forum
 * description:
 * thumbnail:
 * experiment: true
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  name: 'forums',
  defaults: {
    itemView: 'forum.itemView.mainCard'
  }
});
