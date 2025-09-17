/**
 * @type: route
 * name: core.search
 * path: /search/:view?
 * chunkName: pages.search
 * bundle: web
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';

type Params = {
  view?: string;
  tab: string;
  q: string;
};

export default function SearchPage(props: any) {
  const { createPageParams, layoutBackend } = useGlobal();
  const pageParams = createPageParams<Params>(props, prev => {
    return {
      q: prev.q ?? '',
      tab: prev.view?.replace(/-/g, '_') || 'all',
      view: prev.view?.replace(/-/g, '_') || 'all',
      appName: 'search',
      resourceName: 'search',
      module_name: 'search',
      resource_name: 'search',
      pageMetaName: 'search.search.landing',
      _pageType: 'searchGlobal'
    };
  });

  const preferPageName = `core.search.${pageParams.tab}`;

  const pageName = layoutBackend.hasPage(preferPageName)
    ? preferPageName
    : 'core.search';

  return <Page pageName={pageName} pageParams={pageParams} />;
}
