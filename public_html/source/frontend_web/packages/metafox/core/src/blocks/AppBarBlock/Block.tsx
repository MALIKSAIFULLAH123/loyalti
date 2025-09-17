/**
 * @type: block
 * name: core.block.appbar
 * title: Primary Menu
 * keywords: general
 * description: Primary Menu
 * chunkName: block.home
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './AppBar';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {},
  overrides: {
    noHeader: true,
    noFooter: true
  }
});
