/**
 * @type: block
 * name: core.breadcrumbBlock
 * title: Breadcrumb
 * chunkName: block_majority
 * experiment: true
 */

import { connectAppUI, createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectAppUI(Base);

export default createBlock<Props>({
  name: 'core.breadcrumbBlock',
  extendBlock: Enhance,
  defaults: {
    blockLayout: 'main breadcrumb'
  }
});
