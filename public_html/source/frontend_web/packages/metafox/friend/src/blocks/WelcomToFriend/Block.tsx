/**
 * @type: block
 * name: friend.block.welcome
 */
import { BlockViewProps, createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<BlockViewProps>({
  name: 'FriendWelcomeBlock',
  extendBlock: Base
});
