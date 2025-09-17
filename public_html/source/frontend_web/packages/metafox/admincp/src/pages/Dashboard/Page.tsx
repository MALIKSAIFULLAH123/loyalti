/**
 * @type: route
 * name: core.admincp.dashboard
 * path: /admincp, /
 * chunkName: pages.admincp
 * admincp: true
 * bundle: admincp
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';

export default function HomePage(props: unknown) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams(props, () => ({
    appName: 'core',
    pageMetaName: 'admin.core.dashboard'
  }));

  return <Page pageName="core.admincp.dashboard" pageParams={pageParams} />;
}
