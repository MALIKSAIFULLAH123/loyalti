/**
 * @type: route
 * name: core.admincp.browse.datagrid
 * path: /:appName/:resourceName/browse/:name?
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { uniq } from 'lodash';
import React from 'react';

const makeGridName = (app: string, resource: string, name?: string): string => {
  return uniq(`${app}.${resource}${name ? `.${name}` : ''}`.split('.')).join(
    '.'
  );
};

const modifiedName = (name: string): string => name.replace(/-/g, '_');

export default function AdminBrowseDataGridPage(props) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<{
    readonly name: string;
    readonly resourceName: string;
    readonly appName: string;
    readonly gridName: string;
  }>(
    props,
    ({ appName, resourceName, name }) => ({
      gridName: makeGridName(appName, resourceName, name)
    }),
    ({ appName, gridName, resourceName }) => ({
      dataGridProps: {
        appName: modifiedName(appName),
        resourceName: modifiedName(resourceName),
        gridName
      }
    })
  );

  return (
    <Page pageName="core.admincp.browse.datagrid" pageParams={pageParams} />
  );
}
