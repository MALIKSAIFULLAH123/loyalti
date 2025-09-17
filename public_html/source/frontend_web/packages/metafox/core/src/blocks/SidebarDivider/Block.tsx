/**
 * @type: block
 * name: core.dividerBlock
 * keywords: sidebar
 * title: Sidebar Divider
 * chunkName: sidebar
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  overrides: {
    dividerVariant: 'middle',
    noHeader: true
  },
  defaults: {
    title: 'Divider',
    dividerVariant: 'middle',
    blockLayout: 'sidebar divider'
  }
});
