/**
 * @type: block
 * name: subscription.block.packages
 * title: Packages Block
 * keywords: subscription
 * description: Packages page
 */

import { BlockViewProps, createBlock } from '@metafox/framework';
import ItemVIew from './Base';

export default createBlock<BlockViewProps>({
  extendBlock: ItemVIew,
  defaults: {
    title: 'Packages'
  }
});
