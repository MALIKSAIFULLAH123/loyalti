/**
 * @type: block
 * name: advertise.block.sponsorship
 * title: Sponsorship advertise
 * keywords: advertise
 * description: Display sponsorship advertise
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
