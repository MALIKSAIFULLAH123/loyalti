/**
 * @type: route
 * name: core.admincp.setting.edit
 * path: /:appName/setting/:resourceName?
 * chunkName: pages.admincp
 * bundle: admincp
 * priority: 9999
 */
import {
  RemoteDataSource,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import { normalizePageName } from '@metafox/utils';
import React from 'react';

type State = {
  readonly appName: string;
  readonly resourceName: string;
  readonly dataSource: RemoteDataSource;
};

export default function AdminEditSetting(props: any) {
  const { createPageParams } = useGlobal();
  const { appName, resourceName } = props;
  const dataSource = useResourceAction(appName, resourceName, 'settingForm');

  const pageParams = createPageParams<State>(
    props,
    ({ appName, resourceName }) => {
      return {
        appName,
        resourceName,
        pageMetaName: normalizePageName(
          appName,
          resourceName,
          false,
          false,
          'setting'
        ),
        dataSource: dataSource || {
          apiUrl: resourceName
            ? `/admincp/setting/form/${appName}/${resourceName}`
            : `/admincp/setting/form/${appName}`
        }
      };
    }
  );

  return <Page pageName={'core.admincp.setting'} pageParams={pageParams} />;
}
