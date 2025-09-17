/**
 * @type: route
 * name: notification.setting_channel
 * path: /settings/notifications/:channel
 * chunkName: pages.notification
 * bundle: web
 */
import { createLandingPage } from '@metafox/framework';

export default createLandingPage({
  appName: 'user',
  pageName: 'notification.setting_channel',
  loginRequired: true,
  resourceName: 'settings',
  viewResource: 'notificationSettingsByChannel'
});
