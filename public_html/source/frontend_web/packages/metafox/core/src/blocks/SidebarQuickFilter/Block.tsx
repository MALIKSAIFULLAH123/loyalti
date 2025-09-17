/**
 * @type: block
 * name: core.block.sidebarQuickFilter
 * title: Search Form
 * keywords: sidebar
 * description: Add application search form into sidebar arear.
 * chunkName: sidebarHome
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'Filters',
    blockLayout: 'sidebar app filter'
  }
});
