/**
 * @type: route
 * name: core.admincp.overview
 * path: /:appName/overview/:type
 * chunkName: pages.admincp
 * bundle: admincp
 * priority: 9999
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { normalizePageName } from '@metafox/utils';
import { uniq } from 'lodash';
import React from 'react';

const modifiedName = (name: string): string => name?.replace(/-/g, '_');

const makeGridName = (app: string, resource: string, name?: string): string => {
  return uniq(`${app}.${resource}${name ? `.${name}` : ''}`.split('.')).join(
    '.'
  );
};

export default function AdminOverview(props) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<{
    readonly type: string;
    readonly appName: string;
    readonly gridName: string;
  }>(
    props,
    ({ appName, type }) => ({
      gridName: makeGridName(appName, type),
      pageMetaName: normalizePageName(appName, type, 'overview'),
      dataSource: {
        apiUrl: `/admincp/${appName}/overview/${type}`
      }
    }),
    ({ appName, gridName, type }) => ({
      dataGridProps: {
        appName: modifiedName(appName),
        resourceName: modifiedName(type),
        gridName
      }
    })
  );

  return <Page pageName="core.admincp.overview" pageParams={pageParams} />;
}
