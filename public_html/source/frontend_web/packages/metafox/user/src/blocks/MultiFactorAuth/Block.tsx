/**
 * @type: block
 * name: setting.block.multiFactorAuth
 * title: User Settings - MultiFactorAuth
 * keywords: user, settings
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhancer = connect(
  (state: GlobalState) => state.user.multiFactorAuthSettings
)(Base);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'authentication',
    blockLayout: 'Account Setting'
  }
});
