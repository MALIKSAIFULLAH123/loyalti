/**
 * @type: block
 * name: poll.view.pollListingBlock
 * title: Polls
 * keywords: poll
 * description: Display poll items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const PollListingBlock = createBlock<ListViewBlockProps>({
  name: 'PollListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'poll',
    dataSource: { apiUrl: '/poll' }
  },
  defaults: {
    title: 'polls',
    itemView: 'blog.itemView.mainCard'
  }
});

export default PollListingBlock;
