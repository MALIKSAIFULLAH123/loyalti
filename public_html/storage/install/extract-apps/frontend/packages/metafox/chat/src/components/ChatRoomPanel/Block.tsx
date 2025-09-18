/**
 * @type: block
 * name: chat.block.chatroom
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'ChatRoomPanel',
  extendBlock: Base
});
