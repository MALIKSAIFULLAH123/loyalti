import {
  GET_STATICS,
  PageCreatorConfig,
  PageParams,
  useAbortControl,
  useGlobal,
  useLocation
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import { createElement, useCallback, useEffect, useState } from 'react';
import { fetchDetail } from '../actions';
import { useResourceAction } from '../hooks';
import qs from 'query-string';
interface Params extends PageParams {
  resourceName: string;
}

interface Config<T> extends PageCreatorConfig<T> {
  readonly idName?: string;
  readonly resourceName: string;
}

export default function createViewItemPage<T extends Params = Params>({
  appName,
  resourceName,
  pageName,
  idName = 'id',
  loginRequired = false,
  paramCreator
}: Config<T>) {
  function ViewItemDetail(props: any) {
    const { dispatch, createErrorPage, createPageParams, jsxBackend } =
      useGlobal();
    const [loadingDetail, setLoadingDetail] = useState<boolean>(true);
    const [loadingStatics, setLoadingStatics] = useState<boolean>(true);
    const [err, setErr] = useState<number>(0);
    const abortId = useAbortControl();
    const location = useLocation();
    const searchParams = location?.search
      ? qs.parse(location.search.replace(/^\?/, ''))
      : {};

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

    const onSuccessStatics = useCallback(() => {
      setLoadingStatics(false);
    }, []);

    const onSuccessDetail = useCallback(() => {
      setLoadingDetail(false);
    }, []);

    useEffect(() => {
      dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams]);

    useEffect(() => {
      if (err || loadingDetail) return;

      dispatch({
        type: `createViewItemPage/${resourceName}`,
        payload: { identity: pageParams.identity }
      });
      dispatch({
        type: 'createPage/Done',
        payload: { identity: pageParams.identity }
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [loadingDetail, err, pageParams.identity]);

    useEffect(() => {
      dispatch({
        type: GET_STATICS,
        payload: { pageParams },
        meta: { onSuccessStatics }
      });

      if (config) {
        dispatch(
          fetchDetail(
            config.apiUrl,
            {
              apiParams: { ...searchParams, ...(config.apiParams || {}) },
              pageParams
            },
            onSuccessDetail,
            onFailure,
            abortId
          )
        );
      }

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams.identity, pageParams]);

    if (err) {
      return createErrorPage(err, { loginRequired });
    }

    if (loadingDetail || loadingStatics)
      return jsxBackend.render({ component: 'Loading' });

    return createElement(Page, {
      pageName,
      pageParams,
      loginRequired
    });
  }

  ViewItemDetail.displayName = `ViewItemDetail(${pageName})`;

  return ViewItemDetail;
}
