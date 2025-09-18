/**
 * @type: route
 * name: advertise.sponsorship.admincp.setting
 * path: /advertise/sponsor/setting
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';
import { RESOURCE_SPONSOR, APP_NAME } from '@metafox/advertise/constants';

type PageState = {
  readonly appName: string;
  readonly role_id?: string;
};

export default function AdminEditPermission(props) {
  const { createPageParams } = useGlobal();

  const dataSource = useResourceAction(
    APP_NAME,
    RESOURCE_SPONSOR,
    'editSettingForm'
  );
  const pageParams = createPageParams<PageState>(props, prev => ({
    appName: 'advertise',
    pageMetaName: 'admin.advertise.sponsor.setting',
    dataSource
  }));

  return (
    <Page
      pageName="advertise.sponsor.admincp.setting"
      pageParams={pageParams}
    />
  );
}
