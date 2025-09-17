/**
 * @type: block
 * name: core.block.searchBox
 * title: Search Box
 * keywords: sidebar
 * description: Display search box.
 * thumbnail:
 */

import { connectAppUI, createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectAppUI(Base);
// Enhance.displayName = 'core.block.searchBox';

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search',
    title: 'Search Box',
    blockLayout: 'sidebar search box'
  }
});
