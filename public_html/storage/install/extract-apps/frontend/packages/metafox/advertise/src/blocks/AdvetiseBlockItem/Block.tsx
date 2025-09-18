/**
 * @type: block
 * name: advertise.block.advertisesBlockItem
 * title: Advertises Block Item
 * keywords: advertise item
 * description: Display list advertises
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'advertisesBlockItem',

  extendBlock: Base,
  defaults: {
    title: 'Advertises Block Item',
    blockLayout: 'Advertise - All Placement (Top spacing)'
  }
});
