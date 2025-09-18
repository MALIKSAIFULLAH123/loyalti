/**
 * @type: block
 * name: pages.block.pagesClaimsBlock
 * title: Page Claim
 * keywords: page
 * description: Display claim listing
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'PageClaim'
});
