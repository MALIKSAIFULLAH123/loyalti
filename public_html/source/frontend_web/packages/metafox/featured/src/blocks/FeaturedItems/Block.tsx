/**
 * @type: block
 * name: featured.block.featuredItems
 * title: All featured items
 * keywords: featured
 * description: Display all my featured.
 * thumbnail:
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
