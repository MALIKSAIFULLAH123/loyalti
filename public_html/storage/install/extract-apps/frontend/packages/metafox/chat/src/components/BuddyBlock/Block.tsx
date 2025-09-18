/**
 * @type: block
 * name: chat.block.buddy
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const ChatplusBuddyBlock = createBlock<Props>({
  name: 'ChatplusBuddyBlock',
  extendBlock: Base,
  defaults: {
    title: 'messages'
  }
});

export default ChatplusBuddyBlock;
