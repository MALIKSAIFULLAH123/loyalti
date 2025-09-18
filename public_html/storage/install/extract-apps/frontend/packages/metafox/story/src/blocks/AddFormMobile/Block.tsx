/**
 * @type: block
 * name: story.block.addFormViewMobile
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const StoryAddFormBlock = createBlock<Props>({
  name: 'StoryAddFormMobile',
  extendBlock: Base
});

export default StoryAddFormBlock;
