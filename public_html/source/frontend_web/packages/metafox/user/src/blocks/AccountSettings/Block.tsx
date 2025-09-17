/**
 * @type: block
 * name: setting.block.general
 * title: User Setting - General
 * keywords: user, settings
 * description: User Setting General
 * thumbnail:
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhancer = connect((state: GlobalState) => state.user.accountSettings)(
  Base
);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'account_settings',
    blockLayout: 'Account Setting'
  }
});
