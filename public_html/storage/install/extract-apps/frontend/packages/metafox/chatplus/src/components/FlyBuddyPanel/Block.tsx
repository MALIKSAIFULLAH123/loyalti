/**
 * @type: block
 * name: chatplus.block.contactsNewFeed
 */

import { createBlock } from '@metafox/framework';
import BuddyPanel, { Props } from './BuddyPanel';

const ChatplusContactsBlock = createBlock<Props>({
  name: 'ChatplusContactsBlock',
  extendBlock: BuddyPanel,
  defaults: {
    title: 'online_friends',
    blockLayout: 'Blocker Contacts ChatPlus',
    showWhen: ['truthy', 'setting.chatplus.server']
  }
});

export default ChatplusContactsBlock;
