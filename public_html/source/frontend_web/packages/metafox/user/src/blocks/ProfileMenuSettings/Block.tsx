/**
 * @type: block
 * name: setting.block.profile-menu
 * title: User Settings - Profile Menu Settingss
 * keywords: settings, user
 * experiment: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const mapStateToProps = (state: GlobalState) => state.user.profileMenu;
const Enhancer = connect(mapStateToProps)(Base);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'profile_menu_settings',
    blockLayout: 'Account Setting'
  }
});
