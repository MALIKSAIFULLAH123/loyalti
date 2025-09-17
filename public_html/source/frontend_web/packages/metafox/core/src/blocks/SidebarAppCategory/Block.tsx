/**
 * @type: block
 * name: core.categoryBlock
 * keywords: sidebar
 * title: Sidebar Categories
 * chunkName: sidebar
 */

import { connectAppUI, createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectAppUI(Base);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    title: 'Categories',
    blockLayout: 'sidebar app category'
  }
});
