/**
 * @type: block
 * name: setting.block.payment
 * title: User Settings - Payment
 * keywords: user, settings
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhancer = connect((state: GlobalState) => state.user.paymentSettings)(
  Base
);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'payment_settings',
    blockLayout: 'Account Setting'
  }
});
