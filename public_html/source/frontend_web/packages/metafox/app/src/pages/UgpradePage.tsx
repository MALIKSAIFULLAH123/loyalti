/**
 * @type: route
 * path: /app/upgrade
 * name: admincp.app.upgrade
 * bundle: admincp
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

export default function UpgradePage(props) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams({
    appName: 'app',
    pageMeta: 'admin.app.upgrade_apps'
  });

  return <Page pageName="admincp.app.upgrade" pageParams={pageParams} />;
}
