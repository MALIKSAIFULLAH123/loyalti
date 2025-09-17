/**
 * @type: block
 * name: setting.block.sms_notifications
 * title: User Settings - SMS Notifications
 * keywords: user, settings
 * experiment: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhancer = connect(
  (state: GlobalState) => state.user.smsNotificationSettings
)(Base);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'sms_notifications',
    blockLayout: 'Account Setting'
  }
});
