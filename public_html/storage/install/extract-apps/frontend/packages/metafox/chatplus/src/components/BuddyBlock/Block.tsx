/**
 * @type: block
 * name: chatplus.block.buddy
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const ChatplusBuddyBlock = createBlock<Props>({
  name: 'ChatplusBuddyBlock',
  extendBlock: Base
});

export default ChatplusBuddyBlock;
