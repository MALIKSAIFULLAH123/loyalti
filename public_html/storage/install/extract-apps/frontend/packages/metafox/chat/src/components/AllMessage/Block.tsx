/**
 * @type: block
 * name: chat.block.allMessage
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const ChatAllMessageBlock = createBlock<Props>({
  name: 'ChatAllMessageBlock',
  extendBlock: Base
});

export default ChatAllMessageBlock;
