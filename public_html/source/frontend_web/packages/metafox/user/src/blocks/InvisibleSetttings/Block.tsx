/**
 * @type: block
 * name: setting.block.invisible
 * title: User Settings - Invisible Settings
 * keywords: user
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhancer = connect((state: GlobalState) => state.user.invisibleSettings)(
  Base
);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'invisible_settings',
    blockLayout: 'Account Setting'
  }
});
