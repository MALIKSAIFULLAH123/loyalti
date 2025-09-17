/* eslint-disable @typescript-eslint/no-unused-vars */
import { PageCreatorConfig, PageParams, useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { createElement, useEffect } from 'react';
import { useSelector } from 'react-redux';
import { useResourceAction, useTraceUpdate } from '../hooks';

interface Params extends PageParams {}

interface Config<T> extends PageCreatorConfig<T> {
  readonly resourceName: string;
  readonly viewResource?: string;
}

export default function createLandingPage<T extends Params = Params>({
  appName,
  pageName,
  resourceName,
  loginRequired = false,
  defaultTab = 'landing',
  viewResource = 'viewAll',
  paramCreator
}: Config<T>) {
  function LandingPage(props: any) {
    const {
      createPageParams,
      createContentParams,
      dispatch,
      useSessionSummary
    } = useGlobal();

    const session = useSessionSummary();
    const config = useResourceAction(appName, resourceName, viewResource);

    const pageParams = createPageParams<T>(
      props,
      () => ({
        appName,
        resourceName,
        tab: defaultTab,
        pageMetaName: `${appName}.${resourceName}.${defaultTab}`,
        authId: session?.user?.id,
        _pageType: 'browseItem'
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
          apiParams: { ...config?.apiParams, ...pageParams }
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

  LandingPage.displayName = `LandingPage(${pageName})`;

  return LandingPage;
}
