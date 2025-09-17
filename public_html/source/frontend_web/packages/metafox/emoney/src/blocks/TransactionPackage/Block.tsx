/**
 * @type: block
 * name: ewallet.block.balance_transactions
 * keyword: ewallet transaction
 * title: Ewallet Transaction
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
