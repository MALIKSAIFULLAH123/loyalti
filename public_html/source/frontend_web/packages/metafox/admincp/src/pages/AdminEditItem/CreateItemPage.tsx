/**
 * @type: route
 * name: core.admincp.item.create
 * path: /:appName/:resourceName/:action(add|create)/:slug([^?#]*)?
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

export default function CreateItemPage(props: any) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<State>(
    props,
    ({ slug, appName, resourceName }) => {
      return {
        pageMetaName: normalizePageName(appName, resourceName, 'create'),
        dataSource: {
          apiUrl: slug
            ? `/admincp/${appName}/${resourceName}/create/${slug}`
            : `/admincp/${appName}/${resourceName}/create`,
          apiParams: props
        }
      };
    }
  );

  return <Page pageName={'core.admincp.setting'} pageParams={pageParams} />;
}
