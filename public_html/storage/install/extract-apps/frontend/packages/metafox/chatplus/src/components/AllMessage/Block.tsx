/**
 * @type: block
 * name: chatplus.block.allMessage
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const ChatplusAllMessageBlock = createBlock<Props>({
  name: 'ChatplusAllMessageBlock',
  extendBlock: Base
});

export default ChatplusAllMessageBlock;
