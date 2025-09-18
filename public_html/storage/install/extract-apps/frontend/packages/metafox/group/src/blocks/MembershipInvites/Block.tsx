/**
 * @type: block
 * name: group.block.membershipInvites
 * title: membership invites
 * keywords: group
 * description: group manage membership invites
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
