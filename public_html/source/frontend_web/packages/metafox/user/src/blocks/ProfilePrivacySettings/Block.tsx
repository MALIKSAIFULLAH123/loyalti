/**
 * @type: block
 * name: setting.block.profile
 * title: User Settings - Profile Settings
 * keywords: settings
 * experiment: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhance = connect((state: GlobalState) => state.user.profilePrivacy)(
  Base
);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    title: 'your_profile_settings',
    blockLayout: 'Account Setting'
  }
});
