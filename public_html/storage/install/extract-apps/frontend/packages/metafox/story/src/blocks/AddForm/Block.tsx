/**
 * @type: block
 * name: story.block.addFormView
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const StoryAddFormBlock = createBlock<Props>({
  name: 'StoryAddForm',
  extendBlock: Base
});

export default StoryAddFormBlock;
