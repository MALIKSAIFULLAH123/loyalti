/**
 * @type: route
 * name: core.admincp.browse.datagrid2
 * path: /:appName/:parentResource/:parentId/:resourceName/browse/:name?
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { uniq } from 'lodash';
import React from 'react';

const makeGridName = (
  app: string,
  parent: string,
  resource: string,
  name?: string,
  parentId?: string
): string => {
  return uniq(
    `${app}.${parent}.${resource}${name ? `.${name}` : ''}${
      parentId ? `/${parentId}` : ''
    }`.split('.')
  ).join('.');
};

interface PageProps {
  readonly name: string;
  readonly resourceName: string;
  readonly appName: string;
  readonly gridName: string;
  readonly parentResource: string;
  readonly parentId: string;
}

const modifiedName = (name: string): string => name.replace(/-/g, '_');

export default function AdminBrowseDataGridPage(props: PageProps) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<PageProps>(
    props,
    ({ appName, parentResource, parentId, resourceName, name }) => ({
      gridName: makeGridName(
        appName,
        parentResource,
        resourceName,
        name,
        parentId
      ),
      [`${parentResource}_id`]: parentId
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
