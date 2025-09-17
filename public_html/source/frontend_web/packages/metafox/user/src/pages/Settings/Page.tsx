/**
 * @type: route
 * name: settings.home
 * path: /settings/:tab?
 * chunkName: pages.user
 * bundle: web
 */
import { createMultiTabPage } from '@metafox/framework';

export default createMultiTabPage({
  appName: 'user',
  pageName: 'setting.home',
  resourceName: 'settings',
  defaultTab: 'general',
  loginRequired: true,
  acceptNoneIdParam: true,
  tabs: {
    general: 'setting.block.general',
    payment: 'setting.block.payment',
    profile: 'setting.block.profile',
    review: 'setting.block.review-posts',
    'scheduled-posts': 'setting.block.schedule-posts',
    invisible: 'setting.block.invisible',
    items: 'setting.block.items',
    blocked: 'setting.block.blocked',
    'profile-menu': 'setting.block.profile-menu',
    notifications: 'setting.block.notifications',
    'email-notifications': 'setting.block.email_notifications',
    mfa: 'setting.block.multiFactorAuth',
    'sms-notifications': 'setting.block.sms_notifications',
    'manage-hidden': 'setting.block.manage-hidden'
  }
});
