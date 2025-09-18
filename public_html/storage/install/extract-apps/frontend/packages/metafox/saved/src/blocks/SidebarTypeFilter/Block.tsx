/**
 * @type: block
 * name: saved.block.sidebarTypeFilter
 * title: Saved Sidebar Type Filter
 * keywords: sidebar
 * experiment: true
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
