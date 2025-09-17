/**
 * @type: block
 * name: core.block.AdminChartStats
 * title: AdminCP - Dashboard Charts
 * bundle: admincp
 * admincp: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'Chart',
    blockLayout: 'Admin - Block - Contained',
    blockProps: {
      blockStyle: {
        sx: {
          height: 'calc(100% - 16px)'
        }
      }
    }
  }
});
