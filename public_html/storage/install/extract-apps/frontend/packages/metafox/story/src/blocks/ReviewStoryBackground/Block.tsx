/**
 * @type: block
 * name: story.block.storyReviewBackground
 * title: Story Review background
 * keywords: story
 * description: Display review marketplace detail
 * thumbnail:
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock({
  name: 'StoryReview',
  extendBlock: Base,
  defaults: {
    blockLayout: 'Review Story'
  }
});
