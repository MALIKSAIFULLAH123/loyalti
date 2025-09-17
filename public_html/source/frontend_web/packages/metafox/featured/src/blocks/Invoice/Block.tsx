/**
 * @type: block
 * name: featured.block.invoice
 * title: Invoice featured
 * keywords: featured
 * description: Display invoice featured
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
