/**
 * @type: route
 * name: user.logout
 * path: /logout
 * bundle: web
 */
import { IS_ADMINCP, useGlobal } from '@metafox/framework';

export default function LogoutPage() {
  const { redirectTo, cookieBackend, getSetting } = useGlobal();
  const redirect_after_logout = getSetting('user.redirect_after_logout');
  const token = cookieBackend.get('token');

  if (token) {
    cookieBackend.remove('token');
    cookieBackend.remove('refreshToken');
    cookieBackend.remove('dateExpiredToken');
    // unregister all service worker when logout
    try {
      navigator.serviceWorker.getRegistrations().then(registrations => {
        for (const registration of registrations) {
          registration.unregister();
        }
      });
    } catch (err) {}
  }

  const redirectUrl = IS_ADMINCP
    ? process.env.MFOX_ADMINCP_URL
    : process.env.MFOX_SITE_URL;

  redirectTo(redirect_after_logout || redirectUrl || '/');

  return null;
}
