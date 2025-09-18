/**
 * @type: block
 * name: activity-point.block.transactions_package
 * keyword: activity point
 * title: Activity
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
