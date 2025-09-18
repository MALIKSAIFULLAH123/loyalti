import {
  PageCreatorConfig,
  PageParams,
  useAbortControl,
  useGlobal,
  fetchDetail,
  useResourceAction
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import React, { createElement, useCallback, useEffect, useState } from 'react';
interface Params extends PageParams {
  resourceName: string;
}

interface Config<T> extends PageCreatorConfig<T> {
  readonly idName?: string;
  readonly resourceName: string;
}

export default function createDashboardLiveStream<T extends Params = Params>({
  appName,
  resourceName,
  pageName,
  idName = 'id',
  loginRequired = false,
  paramCreator
}: Config<T>) {
  function ViewItemDetail(props: any) {
    const {
      dispatch,
      createErrorPage,
      createPageParams,
      jsxBackend,
      useGetItem,
      navigate
    } = useGlobal();
    const [loadingDetail, setLoadingDetail] = useState<boolean>(true);
    const [init, setInit] = useState(false);
    const [err, setErr] = useState<number>(0);
    const abortId = useAbortControl();

    const pageParams = createPageParams(
      props,
      (prev: any) => ({
        appName,
        id: prev[idName],
        module_name: appName,
        resource_name: resourceName,
        resourceName,
        item_type: resourceName,
        pageMetaName: `${appName}.${resourceName}.view_detail`,
        identity: `${appName}.entities.${resourceName}.${prev[idName]}`,
        _pageType: 'viewItem'
      }),
      paramCreator
    );

    const config = useResourceAction(appName, resourceName, 'viewItem');

    const onFailure = useCallback((error: any) => {
      // eslint-disable-next-line no-console
      setErr(error);
    }, []);

    const onSuccessDetail = useCallback(() => {
      setLoadingDetail(false);
    }, []);

    const item = useGetItem(
      `${pageParams?.appName}.entities.${pageParams?.resourceName}.${pageParams?.id}`
    );

    React.useEffect(() => {
      // redirect on first time check
      if (init) return;

      if (!loadingDetail) {
        if (item?.is_streaming && item?.is_owner) {
          setInit(true);
        } else {
          navigate(item?.link);
        }
      }
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [item?.is_streaming, loadingDetail, init]);

    useEffect(() => {
      dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams]);

    useEffect(() => {
      dispatch(
        fetchDetail(
          config.apiUrl,
          { apiParams: config.apiParams, pageParams },
          onSuccessDetail,
          onFailure,
          abortId
        )
      );

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams.identity, pageParams]);

    if (err) {
      return createErrorPage(err, { loginRequired });
    }

    if (loadingDetail) return jsxBackend.render({ component: 'Loading' });

    if (!item?.is_streaming || !item?.is_owner) return null;

    return createElement(Page, {
      pageName,
      pageParams,
      loginRequired
    });
  }

  ViewItemDetail.displayName = `ViewItemDetail(${pageName})`;

  return ViewItemDetail;
}
