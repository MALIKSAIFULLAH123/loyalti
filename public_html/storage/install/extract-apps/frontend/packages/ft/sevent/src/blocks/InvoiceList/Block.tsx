/**
 * @type: block
 * name: sevent_invoice.block.invoice
 * keyword: sevent invoice
 * title: Sevent Invoice
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
