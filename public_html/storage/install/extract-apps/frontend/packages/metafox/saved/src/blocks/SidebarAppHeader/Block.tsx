/**
 * @type: block
 * name: saved.block.sideAppHeaderDetailCollection
 * title: Saved App Header
 * keywords: saved sidebar
 * thumbnail:
 * experiment: true
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
