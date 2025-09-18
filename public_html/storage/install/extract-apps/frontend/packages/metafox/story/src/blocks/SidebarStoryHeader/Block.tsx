/**
 * @type: block
 * name: story.block.sideStoryHeader
 * title: Story Header
 * keywords: sidebar
 * description: General Story Header
 * thumbnail:
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockLayout: 'sidebar app header',
    slotName: 'side',
    title: 'stories',
    icon: 'ico-compose-alt',
    freeze: true
  }
});
