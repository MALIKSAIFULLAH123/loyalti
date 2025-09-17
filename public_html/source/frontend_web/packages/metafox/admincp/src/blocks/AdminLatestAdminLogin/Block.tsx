/**
 * @type: block
 * name: core.block.AdminLatestAdminLogin
 * title: AdminCP - Latest AdminCP Logins
 * bundle: admincp
 * admincp: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'latest_acp_logins',
    blockLayout: 'Admin - Block - Contained'
  }
});
