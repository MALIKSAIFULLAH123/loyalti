/**
 * @type: route
 * name: admincp.app.home
 * path: admincp/:appName
 * priority: 9999
 * chunkName: pages.admincp
 * bundle: admincp
 */

import { useGlobal } from '@metafox/framework';

export default function AdminAppHome(props) {
  const { createPageParams, navigate, getSetting } = useGlobal();

  const pageParams = createPageParams<{
    readonly appName: string;
  }>(props);

  const url = getSetting(
    `core.adminHomePages.${pageParams.appName}`,
    '/admincp'
  );

  navigate(url, { replace: true });

  return null;
}
