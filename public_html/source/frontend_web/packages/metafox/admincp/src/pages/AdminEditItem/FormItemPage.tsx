/**
 * @type: route
 * name: core.admincp.item.form
 * path: /:appName/:resourceName/form-:formName/:slug?
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { RemoteDataSource, useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { normalizePageName } from '@metafox/utils';
import React from 'react';

type State = {
  readonly appName: string;
  readonly resourceName: string;
  readonly slug: string;
  readonly edit: string;
  readonly formName: string;
  readonly dataSource: RemoteDataSource;
};

export default function FormItemPage(props: any) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<State>(
    props,
    ({ slug, appName, resourceName, formName }) => {
      return {
        pageMetaName: normalizePageName(appName, resourceName, formName),
        dataSource: {
          apiUrl: slug
            ? `/admincp/${appName}/${resourceName}/${slug}/form-${formName}`
            : `/admincp/${appName}/${resourceName}/form-${formName}`
        }
      };
    }
  );

  return <Page pageName={'core.admincp.setting'} pageParams={pageParams} />;
}
