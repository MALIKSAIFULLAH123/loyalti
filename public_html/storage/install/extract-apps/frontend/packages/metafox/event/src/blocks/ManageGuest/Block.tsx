/**
 * @type: block
 * name: event.block.manage_guest
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockLayout: 'Main Form'
  }
});
