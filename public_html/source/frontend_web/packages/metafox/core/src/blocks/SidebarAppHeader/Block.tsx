/**
 * @type: block
 * name: core.block.sideAppHeader
 * title: App Header
 * keywords: sidebar
 * description: General App Header
 * chunkName: sidebar
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'App Header',
    blockLayout: 'sidebar app header'
  }
});
