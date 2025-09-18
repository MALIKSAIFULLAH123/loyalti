/**
 * @type: block
 * name: story.block.listingBlock
 * title: Story Listing Block
 * keywords: story
 * description: Display story listing
 * chunkName: story
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockLayout: 'Story - Listing - Feed',
    itemView: 'story.itemView.storyCard',
    gridLayout: 'Story - Story Card',
    itemLayout: 'Story - Story Card',
    canLoadMore: true
  },
  overrides: {
    showWhen: ['truthy', 'setting.story']
  }
});
