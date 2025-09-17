/**
 * @type: route
 * name: user.admincp.logout
 * path: /logout
 * chunkName: pages.admincp
 * bundle: admincp
 */

import { useGlobal } from '@metafox/framework';

export default function AdminLogout() {
  const { redirectTo, cookieBackend } = useGlobal();

  const token = cookieBackend.get('token');

  if (token) {
    cookieBackend.remove('token');
    cookieBackend.remove('refreshToken');
    cookieBackend.remove('dateExpiredToken');
  }

  const redirectUrl = process.env.MFOX_ADMINCP_URL;

  redirectTo(redirectUrl || '/');

  return null;
}
