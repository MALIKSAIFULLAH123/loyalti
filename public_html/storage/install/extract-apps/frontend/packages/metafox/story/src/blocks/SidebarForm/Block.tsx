/**
 * @type: block
 * name: story.block.sideBarForm
 * title: Form Sidebar
 * keywords: sidebar
 * description:
 * thumbnail:
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockLayout: 'sidebar form story',
    backProps: {
      to: '/story',
      title: 'stories'
    }
  }
});
