/**
 * @type: block
 * name: advertise.block.allAds
 * title: All advertise
 * keywords: advertise
 * description: Display all my advertises
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
