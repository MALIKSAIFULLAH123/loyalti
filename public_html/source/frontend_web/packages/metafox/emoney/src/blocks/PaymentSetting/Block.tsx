/**
 * @type: block
 * name: ewallet.block.paymentSetting
 * title: Ewallet User Settings - Payment
 * keywords: ewallet payment setting
 * experiment: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import Base, { Props } from './Base';
import { connect } from 'react-redux';

const Enhancer = connect((state: GlobalState) => state.user.paymentSettings)(
  Base
);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'payment_gateways',
    blockLayout: 'Account Setting'
  }
});
