/**
 * @type: block
 * name: setting.block.manage-hidden
 * title: User Settings - Manage Hidden
 * keywords: user, settings, manage-hidden
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'manage_hidden',
    blockLayout: 'Account Setting'
  }
});
