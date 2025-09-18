/**
 * @type: block
 * name: chatplus.block.callview
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const CallViewBlock = createBlock<Props>({
  name: 'CallViewBlock',
  extendBlock: Base
});

export default CallViewBlock;
