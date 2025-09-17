/**
 * @type: block
 * name: setting.block.notifications
 * title: User Settings - Notifications
 * keywords: user, settings
 * experiment: true
 */
 import { createBlock, GlobalState } from '@metafox/framework';
 import { connect } from 'react-redux';
 import Base, { Props } from './Base';
 
 const Enhancer = connect(
   (state: GlobalState) => state.user.notificationSettings
 )(Base);
 
 export default createBlock<Props>({
   extendBlock: Enhancer,
   defaults: {
     title: 'notifications',
     blockLayout: 'Account Setting'
   }
 });
 