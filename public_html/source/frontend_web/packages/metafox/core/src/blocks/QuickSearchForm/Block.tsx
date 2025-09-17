/**
 * @type: block
 * name: core.block.quickSearchForm
 * title: Quick Search Form
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'QuickSearchForm',
    blockLayout: 'Block Full Main'
  }
});
