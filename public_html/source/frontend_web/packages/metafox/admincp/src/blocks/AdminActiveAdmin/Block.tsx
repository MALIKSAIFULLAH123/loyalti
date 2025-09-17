/**
 * @type: block
 * name: core.block.AdminActiveAdmin
 * title: AdminCP - Active Admins
 * bundle: admincp
 * admincp: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockLayout: 'Admin - Block - Contained'
  }
});
