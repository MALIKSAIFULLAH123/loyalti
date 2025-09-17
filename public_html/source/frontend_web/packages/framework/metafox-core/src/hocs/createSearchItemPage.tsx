import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { createElement, useEffect } from 'react';
import { useResourceAction } from '../hooks';
import { PageCreatorConfig, PageParams } from '../types';

interface Params extends PageParams {
  tab: string;
  view: string;
  sort: string;
  when: string;
  search: string;
  category?: string[];
  defaultTab?: string;
  appName: string;
  hashtag: string;
}
interface Config<T> extends PageCreatorConfig<T> {
  filterValuesCreator?: () => Partial<T>;
  viewResource?: string;
  apiParamsResourceDefault?: boolean;
  breadcrumb?: boolean;
  headingMeta?: boolean;
  backPage?: boolean;
}

export default function createSearchItemPage<T extends Params = Params>({
  appName,
  pageType,
  pageName,
  resourceName,
  defaultTab = 'all',
  loginRequired = false,
  viewResource = 'viewAll',
  apiParamsResourceDefault = false,
  headingMeta,
  paramCreator,
  breadcrumb = true,
  backPage = true,
  backPageProps
}: Config<T>) {
  function BrowsePage(props: any) {
    const {
      createPageParams,
      i18n,
      createContentParams,
      dispatch,
      usePageMeta
    } = useGlobal();

    const config = useResourceAction(appName, resourceName, viewResource);
    const data = usePageMeta();
    const { 'og:title': ogTitle } = data || {};
    const metaTitle = headingMeta ? ogTitle : '';
    const pageParams = createPageParams<T>(
      props,
      prev => ({
        tab: prev.tab ?? defaultTab,
        appName,
        resourceName,
        breadcrumb,
        heading: metaTitle || i18n.formatMessage({ id: 'search_results' }),
        pageMetaName: `${appName}.${resourceName}`,
        backPage,
        backPageProps,
        _pageType: pageType || 'searchItem',
        identity: prev?.id
          ? `${appName}.entities.${resourceName}.${prev?.id}`
          : undefined
      }),
      paramCreator
    );
    const contentParams = createContentParams({
      mainListing: {
        canLoadMore: true,
        contentType: resourceName,
        title: pageParams.heading,
        dataSource: {
          apiUrl: config?.apiUrl,
          apiRules: config?.apiRules,
          apiParams: apiParamsResourceDefault
            ? { ...config?.apiParams, ...pageParams }
            : pageParams
        }
      }
    });

    useEffect(() => {
      dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams]);

    return createElement(Page, {
      pageName,
      pageParams,
      contentParams,
      loginRequired
    });
  }

  return BrowsePage;
}
