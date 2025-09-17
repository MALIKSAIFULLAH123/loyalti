/**
 * @type: block
 * name: core.block.AdminMaintainCache
 * title: AdminCP - Maintain Cache
 * bundle: admincp
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'Maintain Cache'
  }
});
