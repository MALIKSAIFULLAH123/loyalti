/**
 * @type: block
 * name: subscription_comparison.block.comparisonBlock
 * title: Comparison
 * keywords: subscription
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'Comparison',
  defaults: {
    title: 'Packages Comparison',
    blockLayout: 'Subscription Table Comparison'
  }
});
