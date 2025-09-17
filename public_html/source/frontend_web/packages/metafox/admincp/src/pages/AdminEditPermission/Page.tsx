/**
 * @type: route
 * name: user.admincp.permission
 * path: /:appName/permission
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

type PageState = {
  readonly appName: string;
  readonly role_id?: string;
};

export default function AdminEditPermission(props) {
  const { createPageParams } = useGlobal();

  const dataSource = useResourceAction(
    'authorization',
    'user_permission',
    'editForm'
  );
  const pageParams = createPageParams<PageState>(props, prev => ({
    appName: prev.appName,
    pageMetaName: `admin.${prev.appName}.permissions`,
    dataSource
  }));

  return (
    <Page pageName="user.admincp.permission.manage" pageParams={pageParams} />
  );
}
