/**
 * @type: route
 * name: core.admincp.statistic
 * path: /:appName/:resourceName/statistic, /:appName/statistic
 * chunkName: pages.admincp
 * bundle: admincp
 * priority: 9999
 */
import { RemoteDataSource, useGlobal, useLocation } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

type State = {
  readonly appName: string;
  readonly resourceName: string;
  readonly dataSource: RemoteDataSource;
};

export default function EditItemPage(props: any) {
  const { createPageParams } = useGlobal();
  const { pathname } = useLocation();

  const pageParams = createPageParams<State>(props, ({ appName }) => {
    return {
      appName,
      dataSource: {
        apiUrl: `/admincp${pathname}`,
        apiParams: props
      }
    };
  });

  return <Page pageName="core.admincp.controlled" pageParams={pageParams} />;
}
