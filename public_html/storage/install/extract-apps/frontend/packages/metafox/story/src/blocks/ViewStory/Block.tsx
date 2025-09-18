/**
 * @type: block
 * name: story.block.storyView
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const StoryViewBlock = createBlock<Props>({
  name: 'StoryViewBlock',
  extendBlock: Base
});

export default StoryViewBlock;
