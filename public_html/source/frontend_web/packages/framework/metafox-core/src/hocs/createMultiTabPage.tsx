import {
  AppResourceAction,
  getDeepItem,
  GlobalState,
  useGlobal
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import { filterShowWhen } from '@metafox/utils';
import React, { createElement, useEffect } from 'react';
import { useSelector } from 'react-redux';
import { fetchDetail } from '../actions';
import { useResourceAction } from '../hooks';
import { PageCreatorConfig, PageParams } from '../types';
import { get } from 'lodash';

interface Params extends PageParams {}

interface Config<T> extends PageCreatorConfig<T> {
  readonly tabs: Record<string, string>;
  readonly idName?: string;
  readonly showWhen?: Array<any>;
  readonly heading?: string;
  readonly acceptNoneIdParam?: boolean;
  readonly defaultTabKey?: boolean;
  breadcrumb?: boolean;
}

export default function createMultiTabPage<T extends Params = Params>({
  pageName,
  appName,
  resourceName,
  tabs,
  defaultTab = 'general',
  defaultTabKey,
  loginRequired = false,
  idName = 'id',
  showWhen,
  breadcrumb = true,
  acceptNoneIdParam = false,
  heading
}: Config<T>) {
  function Base(props: any) {
    const {
      createPageParams,
      createErrorPage,
      createContentParams,
      dispatch,
      jsxBackend
    } = useGlobal();

    const [err, setErr] = React.useState<number>(0);
    const [loading, setLoading] = React.useState(true);
    const [cacheKey, setCacheKey] = React.useState<number>(0);

    const config: AppResourceAction = useResourceAction(
      appName,
      resourceName,
      'viewItem'
    );

    const item = useSelector<GlobalState, T>(state =>
      getDeepItem(state, `${appName}.entities.${resourceName}.${props.id}`)
    );

    const pageParams = createPageParams<{
      appName: string;
      tab: string;
      identity: string;
    }>({ ...props, cacheKey }, prev => ({
      appName,
      resourceName,
      heading,
      breadcrumb,
      tab: prev.tab || get(item, defaultTabKey) || defaultTab,
      pageMetaName: `${appName}.${resourceName}.${prev.tab || defaultTab}`,
      identity: prev[idName]
        ? `${appName}.entities.${resourceName}.${prev[idName]}`
        : undefined,
      _pageType: 'browseItem'
    }));

    const contentParams = createContentParams({
      mainBlock: {
        component: tabs[pageParams.tab]
      }
    });

    useEffect(() => {
      dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams]);

    const id = pageParams[idName];

    useEffect(() => {
      if (!config?.apiUrl) return;

      setLoading(true);
      dispatch(
        fetchDetail(
          config.apiUrl,
          { id },
          () => {
            setCacheKey(Math.random());
          },
          setErr
        )
      );

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [id]);

    useEffect(() => {
      if (err || loading) return;

      dispatch({
        type: 'createPage/Done',
        payload: { identity: pageParams.identity }
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [loading, err, pageParams.identity]);

    useEffect(() => {
      if (acceptNoneIdParam) setLoading(false);

      if (!item) return;

      setLoading(false);
    }, [item]);

    if (err) {
      return createErrorPage(err);
    }

    if (loading) return jsxBackend.render({ component: 'Loading' });

    const show = !!filterShowWhen([{ showWhen }], { item }).length;

    if (!show) return <Page pageName="core.error403" />;

    const preferPageName = `${pageName}.${pageParams.tab}`;

    return createElement(Page, {
      pageName: preferPageName,
      pageNameAlt: pageName,
      loginRequired,
      pageParams,
      contentParams
    });
  }
  Base.displayName = `createMultiTabPage(${pageName})`;

  return Base;
}
