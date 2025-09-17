/**
 * @type: block
 * name: core.block.AdminDepthStats
 * title: AdminCP - Depth Statistics
 * bundle: admincp
 * admincp: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'in_dept_statistics',
    blockLayout: 'Admin - Block - Contained'
  }
});
