/**
 * @type: block
 * name: core.block.AppBarBranch
 * title: Site Branch Logo
 * keywords: general
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base from './AppBarBranch';

export default createBlock({
  extendBlock: Base,
  defaults: {},
  overrides: {
    blockLayout: 'AppBarBranch',
    noHeader: true,
    noFooter: true
  }
});
