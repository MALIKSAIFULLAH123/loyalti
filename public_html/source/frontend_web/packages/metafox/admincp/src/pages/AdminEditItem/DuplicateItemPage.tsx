/**
 * @type: route
 * name: core.admincp.item.duplicate
 * path: /:appName/:resourceName/:action(duplicate)/:slug([^?#]*)?
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { RemoteDataSource, useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

type State = {
  readonly appName: string;
  readonly resourceName: string;
  readonly slug: string;
  readonly edit: string;
  readonly dataSource: RemoteDataSource;
};

export default function DuplicateItemPage(props: any) {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<State>(
    props,
    ({ slug, appName, resourceName }) => {
      return {
        dataSource: {
          apiUrl: slug
            ? `/admincp/${appName}/${resourceName}/${slug}/duplicate`
            : `/admincp/${appName}/${resourceName}/duplicate`,
          apiParams: props
        }
      };
    }
  );

  return (
    <Page pageName={'core.admincp.tabs.setting'} pageParams={pageParams} />
  );
}
