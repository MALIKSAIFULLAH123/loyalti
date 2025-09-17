/**
 * @type: block
 * name: ewallet.block.dashboard
 * keyword: ewallet dashboard
 * title: Activity Points
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
