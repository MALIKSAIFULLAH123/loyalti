/**
 * @type: block
 * name: setting.block.email_notifications
 * title: User Settings - Email Notifications
 * keywords: user, settings
 * experiment: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhancer = connect(
  (state: GlobalState) => state.user.emailNotificationSettings
)(Base);

export default createBlock<Props>({
  extendBlock: Enhancer,
  defaults: {
    title: 'email_notifications',
    blockLayout: 'Account Setting'
  }
});
