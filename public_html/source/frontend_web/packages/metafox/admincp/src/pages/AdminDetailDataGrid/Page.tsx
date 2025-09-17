/**
 * @type: route
 * name: core.admincp.detail.datagrid
 * path: /:appName/:resourceName/detail/:id
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { normalizePageName } from '@metafox/utils';
import { uniq } from 'lodash';
import React from 'react';

const makeGridName = (app: string, resource: string, name?: string): string => {
  return uniq(`${app}.${resource}${name ? `.${name}` : ''}`.split('.')).join(
    '.'
  );
};

const modifiedName = (name: string): string => name.replace(/-/g, '_');

export default function AdminDetailDataGridPage(props) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<{
    readonly name: string;
    readonly resourceName: string;
    readonly appName: string;
    readonly gridName: string;
    readonly id: string;
  }>(
    props,
    ({ appName, resourceName, name }) => {
      return {
        gridName: makeGridName(appName, resourceName, 'detail'),
        pageMetaName: normalizePageName(appName, resourceName, 'view', name)
      };
    },
    ({ appName, gridName, resourceName }) => ({
      dataGridProps: {
        appName: modifiedName(appName),
        resourceName: modifiedName(resourceName),
        gridName,
        actionName: 'searchFormDetail',
        getListingActionName: 'viewDetail'
      }
    })
  );

  return (
    <Page pageName="core.admincp.detail.datagrid" pageParams={pageParams} />
  );
}
