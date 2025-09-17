/**
 * @type: route
 * name: core.admincp.app.store
 * path: /app/store/products/browse
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Page } from '@metafox/layout';
import createContentParams from '@metafox/layout/createContentParams';
import React from 'react';

const appName = 'app';
const resourceName = 'app_store_product';
const actionName = 'getSearchForm';

export default function AdminApp(props) {
  const { createPageParams } = useGlobal();

  const config = useResourceAction(appName, resourceName, actionName);

  const pageParams = createPageParams<{
    readonly name: string;
    readonly resourceName: string;
    readonly appName: string;
    readonly gridName: string;
    readonly id: string;
  }>(
    props,
    () => ({
      appName,
      resourceName,
      pageMetaName: 'admin.app.browse_product'
    }),
    () => ({
      dataGridProps: {
        appName,
        resourceName,
        actionName
      },
      appName,
      resourceName
    })
  );

  const contentParams = createContentParams({
    mainListing: {
      canLoadMore: true,
      contentType: resourceName,
      dataSource: {
        apiUrl: config?.apiUrl,
        apiRules: config?.apiRules,
        apiParams: { ...config?.apiParams, ...pageParams }
      }
    }
  });

  return (
    <Page
      pageName="core.admincp.app.store"
      pageParams={pageParams}
      contentParams={contentParams}
    />
  );
}
