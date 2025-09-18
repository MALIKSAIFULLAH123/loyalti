/**
 * @type: block
 * name: chatplus.block.allMessageMobile
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const ChatplusAllMessageBlock = createBlock<Props>({
  name: 'ChatplusAllMessageMobileBlock',
  extendBlock: Base
});

export default ChatplusAllMessageBlock;
