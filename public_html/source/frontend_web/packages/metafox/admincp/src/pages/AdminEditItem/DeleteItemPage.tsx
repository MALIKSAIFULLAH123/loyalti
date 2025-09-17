/**
 * @type: route
 * name: core.admincp.item.delete
 * path: /:appName/:resourceName/:action(delete)/:slug([^?#]*)?
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
  readonly action: string;
  readonly dataSource: RemoteDataSource;
};

export default function DeleteItemPage(props: any) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<State>(
    props,
    ({ slug, appName, resourceName }) => {
      return {
        pageMetaName: normalizePageName(appName, resourceName, 'delete'),
        dataSource: {
          apiUrl: slug
            ? `/admincp/${appName}/${resourceName}/${slug}/delete`
            : `/admincp/${appName}/${resourceName}/delete`
        }
      };
    }
  );

  return <Page pageName={'core.admincp.setting'} pageParams={pageParams} />;
}
