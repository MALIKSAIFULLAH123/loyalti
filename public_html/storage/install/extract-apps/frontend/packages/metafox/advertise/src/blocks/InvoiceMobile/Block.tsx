/**
 * @type: block
 * name: advertise.block.invoiceMobile
 * title: Invoice advertise
 * keywords: advertise
 * description: Display invoice advertise mobile
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
