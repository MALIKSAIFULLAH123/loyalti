/**
 * @type: block
 * name: event.block.manage_invite
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from '@metafox/core/blocks/EditingForm';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    appName: 'event',
    resourceName: 'event',
    formName: 'manageInvite',
    blockLayout: 'Main Form'
  }
});
