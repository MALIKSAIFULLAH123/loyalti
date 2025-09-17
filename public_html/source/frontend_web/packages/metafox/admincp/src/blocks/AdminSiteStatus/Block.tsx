/**
 * @type: block
 * name: core.block.AdminSiteStatus
 * title: AdminCP - Site Status
 * bundle: admincp
 * admincp: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import Base, { Props } from './Base';
import { connect } from 'react-redux';

const Enhance = connect((state: GlobalState) => ({
  data: state.admincp.status
}))(Base);
export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    title: 'site_status',
    blockLayout: 'Admin - Block - Contained'
  }
});
