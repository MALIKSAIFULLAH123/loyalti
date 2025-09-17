/**
 * @type: block
 * name: setting.block.blocked
 * title: User Settings - Blocked Members
 * keywords: user, settings
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'blocked_users',
    blockLayout: 'Blocked Setting Mobile'
  }
});
