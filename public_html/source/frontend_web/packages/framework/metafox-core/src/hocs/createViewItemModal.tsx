import {
  PageCreatorConfig,
  PageParams,
  useAbortControl,
  useGlobal,
  useResourceAction,
  useLocation,
  useGetItem
} from '@metafox/framework';
import React, { useEffect } from 'react';
import { fetchDetail } from '../actions';
import qs from 'query-string';
interface Params extends PageParams {
  resourceName: string;
  identity: string;
}

interface Config<T> extends PageCreatorConfig<T> {
  readonly idName?: string;
  readonly resourceName: string;
  readonly component: string;
  dialogId?: string;
}

export default function createViewItemModal<T extends Params = Params>({
  appName,
  resourceName,
  component,
  pageName,
  idName = 'id',
  loginRequired = false,
  paramCreator,
  dialogId
}: Config<T>) {
  function ViewItemModal(props: any) {
    const { createPageParams, dispatch, dialogBackend, use } = useGlobal();
    const abortId = useAbortControl();
    const [error, setErr] = React.useState<number>(0);
    const [loading, setLoading] = React.useState<boolean>(true);

    const pageParams = createPageParams<Params>(props, prev => ({
      appName,
      resourceName,
      _pageType: 'viewItemInModal',
      identity: `${appName}.entities.${resourceName}.${prev[idName]}`
    }));
    const location = useLocation();
    const searchParams = location?.search
      ? qs.parse(location.search.replace(/^\?/, ''))
      : {};

    const onFailure = React.useCallback((error: any) => {
      // eslint-disable-next-line no-console
      setErr(error);
      setLoading(false);
    }, []);

    const onSuccess = React.useCallback(() => {
      setLoading(false);
    }, []);

    useEffect(() => {
      use({ getPageParams: () => pageParams });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams]);

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const config = useResourceAction(appName, resourceName, 'viewItem');

    const { identity } = pageParams;

    const id = pageParams[idName];
    const item = useGetItem(identity);

    useEffect(() => {
      dispatch(
        fetchDetail(
          config.apiUrl,
          { apiParams: { ...searchParams }, id },
          onSuccess,
          onFailure,
          abortId
        )
      );
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [config.apiUrl, id, location?.search]);

    useEffect(() => {
      if (error || loading) return;

      dispatch({
        type: `createViewItemPage/${resourceName}`,
        payload: { identity: pageParams.identity }
      });
      dispatch({
        type: 'createPage/Done',
        payload: { identity: pageParams.identity }
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [loading, error, pageParams.identity]);

    useEffect(() => {
      if (loading && !item)
        dialogBackend.present({
          component: 'Loading',
          dialogId: dialogId ?? `${appName}${resourceName}`
        });
      else
        dialogBackend.present({
          component,
          props: { identity, error, searchParams },
          dialogId: dialogId ?? `${appName}${resourceName}`
        });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [identity, error, loading]);

    return null;
  }

  ViewItemModal.displayName = `createViewItemModal(${pageName})`;

  return ViewItemModal;
}
