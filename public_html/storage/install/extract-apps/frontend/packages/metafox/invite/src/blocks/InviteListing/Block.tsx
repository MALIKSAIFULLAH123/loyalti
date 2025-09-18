/**
 * @type: block
 * name: invite.block.inviteListing
 * title: Invite Listing
 * keywords: invite
 * description: Display invite listing
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'InviteListing'
});
