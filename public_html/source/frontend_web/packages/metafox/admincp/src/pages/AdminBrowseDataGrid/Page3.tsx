/**
 * @type: route
 * name: core.admincp.browse.datagrid3
 * path: /:appName/:ancestorResource/:ancestorId/:parentResource/:parentId/:resourceName/browse/:name?
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { normalizePageName } from '@metafox/utils';
import { uniq } from 'lodash';
import React from 'react';

const makeGridName = (
  app: string,
  ancestor: string,
  parent: string,
  resource: string,
  name?: string,
  parentId?: string
): string => {
  return uniq(
    `${app}.${ancestor}.${parent}.${resource}${name ? `.${name}` : ''}${
      parentId ? `/${parentId}` : ''
    }`.split('.')
  ).join('.');
};

const modifiedName = (name: string): string => name.replace(/-/g, '_');

interface PageProps {
  readonly name: string;
  readonly resourceName: string;
  readonly appName: string;
  readonly gridName: string;
  readonly parentResource: string;
  readonly parentId: string;
  readonly ancestorResource: string;
  readonly ancestorId: string;
}

export default function AdminBrowseDataGridPage(props: PageProps) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<PageProps>(
    props,
    ({
      appName,
      ancestorResource,
      ancestorId,
      parentResource,
      parentId,
      resourceName,
      name
    }) => ({
      gridName: makeGridName(
        appName,
        ancestorResource,
        parentResource,
        resourceName,
        name,
        parentId
      ),
      pageMetaName: normalizePageName(appName, resourceName, 'browse', name),
      [`${parentResource}_id`]: parentId,
      [`${ancestorResource}_id`]: ancestorId
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
