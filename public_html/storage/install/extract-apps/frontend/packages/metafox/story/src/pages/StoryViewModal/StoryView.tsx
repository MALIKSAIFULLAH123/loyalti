/**
 * @type: modalRoute
 * name: story.viewModal
 * path: /story/:user_id(\d+)/:id(\d+)/:slug?
 * bundle: web
 */
import {
  PageParams,
  useAbortControl,
  useGlobal,
  useResourceAction,
  useLocation,
  fetchDetail
} from '@metafox/framework';
import React, { useEffect } from 'react';
import qs from 'query-string';

interface Params extends PageParams {
  resourceName: string;
  identity: string;
}

const appName = 'story';
const resourceName = 'story';
const component = 'story.dialog.storyView';
const dialogId = 'storyMedia';
const idName = 'id';

export default function StoryViewModal(props) {
  const { createPageParams, dispatch, dialogBackend, use } = useGlobal();
  const abortId = useAbortControl();
  const [error, setErr] = React.useState<number>(0);
  const [loading, setLoading] = React.useState<boolean>(true);

  const pageParams = createPageParams<Params>(props, prev => ({
    appName,
    resourceName,
    _pageType: 'viewItemInModal',
    identity: `${appName}.entities.${resourceName}.${prev[idName]}`,
    ...(prev?.tab && {
      tab: prev?.tab
    })
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
    if (loading)
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
