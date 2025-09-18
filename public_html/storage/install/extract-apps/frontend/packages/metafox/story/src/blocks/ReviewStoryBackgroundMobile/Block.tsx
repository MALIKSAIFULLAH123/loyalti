/**
 * @type: block
 * name: story.block.storyReviewBackgroundMobile
 * title: Story Review Mobile
 * keywords: story
 * description:
 * thumbnail:
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock({
  name: 'StoryReviewMobile',
  extendBlock: Base,
  defaults: {
    blockLayout: 'Review Story Mobile'
  }
});
