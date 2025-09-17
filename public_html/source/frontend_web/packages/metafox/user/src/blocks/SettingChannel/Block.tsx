/**
 * @type: block
 * name: notification.block.setting_channel
 * title: setting channel
 * keywords: notification setting channel
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'SettingChanelNotification',
  defaults: {
    blockLayout: 'Account Setting',
    title: 'channel_notifications'
  }
});
