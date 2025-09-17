/**
 * @type: block
 * name: core.block.mainForm
 * title: Main Form
 * keywords: general
 * chunkName: block_majority
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'MainForm',
  defaults: {
    blockLayout: 'Main Form'
  },
  overrides: {
    title: 'Main Form'
  }
});
