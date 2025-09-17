/**
 * @type: block
 * name: ewallet.block.request
 * keyword: ewallet request
 * title: Activity
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
