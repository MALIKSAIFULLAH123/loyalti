/**
 * @type: block
 * name: static.block.mainContent
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'Policy'
  }
});
