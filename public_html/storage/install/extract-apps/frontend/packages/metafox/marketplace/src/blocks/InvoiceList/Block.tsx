/**
 * @type: block
 * name: marketplace_invoice.block.invoice
 * keyword: marketplace
 * title: Marketplace Invoice 
 * description: Display marketplace invoice
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
