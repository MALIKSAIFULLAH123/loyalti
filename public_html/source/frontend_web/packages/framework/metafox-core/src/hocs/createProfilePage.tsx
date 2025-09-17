import {
  AppResourceAction,
  getDeepItem,
  GlobalState,
  PageCreatorConfig,
  PageParams,
  useGlobal,
  useAbortControl,
  useResourceAction
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import React, { createElement, useEffect } from 'react';
import { useSelector } from 'react-redux';
import { fetchDetail } from '../actions';

interface Params extends PageParams {
  resourceName: string;
  _loadedDetail?: boolean;
}

interface Config<T> extends PageCreatorConfig<T> {
  readonly idName?: string;
  readonly resourceName: string;
  loadingTab?: string;
}

export default function createProfilePage<T extends Params = Params>({
  appName,
  resourceName,
  pageName,
  idName = 'id',
  loginRequired = false,
  loadingTab,
  paramCreator
}: Config<T>) {
  function ProfilePage(props: any) {
    const { dispatch, createPageParams, createErrorPage } = useGlobal();
    const [err, setErr] = React.useState<number>(0);
    const [loading, setLoading] = React.useState(true);
    const item = useSelector<GlobalState, T>(state =>
      getDeepItem(state, `${appName}.entities.${resourceName}.${props[idName]}`)
    );
    const pageParams = createPageParams(
      props,
      prev => ({
        appName,
        profile_page: true,
        tab: prev.tab ?? item?.defaultActiveTabMenu ?? 'home',
        module_name: appName,
        resource_name: resourceName,
        resourceName,
        profile_id: prev[idName],
        profile_type: resourceName,
        item_type: resourceName,
        item_id: prev[idName],
        identity: `${appName}.entities.${resourceName}.${prev[idName]}`,
        _pageType: 'profile'
      }),
      ({ tab }) => ({
        pageMetaName: `${appName}.profile.${tab ?? 'home'}`
      }),
      paramCreator
    );

    const abortId = useAbortControl(pageParams.identity);

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const config: AppResourceAction = useResourceAction(
      appName,
      resourceName,
      'viewItem'
    );

    useEffect(() => {
      dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams]);

    useEffect(() => {
      if (err || loading) return;

      dispatch({
        type: `createViewItemPage/${resourceName}`,
        payload: { identity: pageParams.identity }
      });
      dispatch({
        type: 'createPage/Done',
        payload: { identity: pageParams.identity }
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [loading, err, pageParams.identity]);

    useEffect(() => {
      if (!item) return;

      setLoading(false);
    }, [item]);

    // how to handle error
    // 404 Page
    // 403 Page
    // 503 Page
    // 500 Page

    React.useEffect(() => {
      if (!pageParams[idName]) return;

      setLoading(true);
      dispatch(
        fetchDetail(
          config.apiUrl,
          {
            pageParams,
            apiParams: config.apiParams,
            id: pageParams[idName]
          },
          () => {
            setLoading(false);
          },
          setErr,
          abortId
        )
      );

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams[idName]]);

    if (err) {
      return createErrorPage(err);
    }

    if (loadingTab) {
      if (loading && !item?._loadedDetail)
        return createElement(Page, {
          pageName: `${pageName}.${loadingTab}`,
          loginRequired,
          pageParams
        });
    }

    return createElement(Page, {
      pageName: `${pageName}.${pageParams.tab}`,
      loginRequired,
      pageParams
    });
  }

  ProfilePage.displayName = `ProfilePage(${pageName})`;

  return ProfilePage;
}
