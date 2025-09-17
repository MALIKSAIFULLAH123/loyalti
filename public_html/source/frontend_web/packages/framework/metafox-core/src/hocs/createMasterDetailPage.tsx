import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { createElement, useEffect } from 'react';
import { PageCreatorConfig, PageParams } from '../types';
interface Params extends PageParams {}

interface Config<T> extends PageCreatorConfig<T> {
  readonly pageTitle?: string;
  readonly breadcrumb?: boolean;
  readonly pathMap: { from: string; to: string };
  readonly backPage?: boolean;
  readonly backPageProps?: { title: string; to: string };
}

export default function createMasterDetailPage<T extends Params = Params>({
  appName,
  pageName,
  pageTitle,
  pathMap,
  breadcrumb,
  loginRequired = false,
  backPage = false,
  backPageProps,
  paramCreator
}: Config<T>) {
  function View(props: any) {
    const { useMasterPageConfig, i18n, createPageParams, dispatch } =
      useGlobal();

    useEffect(() => {
      if (pageName === 'friend.masterDetail') {
        dispatch({
          type: 'core/status/clearFriend'
        });
      }
    }, [dispatch]);

    const pageParams = createPageParams<T>(
      props,
      () => ({
        breadcrumb,
        pageTitle: i18n.formatMessage({ id: pageTitle }),
        _pageType: 'browseItem',
        appName,
        backPage,
        pageMetaName: `${appName}.master_detail`,
        backPageProps
      }),
      paramCreator
    );

    const master = useMasterPageConfig((pathname: string) => {
      return pathMap?.from === pathname ? pathMap.to : pathname;
    });

    return createElement(Page, { pageName, pageParams, master, loginRequired });
  }

  View.displayName = `PageMaster_${appName}`;

  return View;
}
