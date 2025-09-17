/**
 * @type: route
 * name: photo.view
 * path: /media/:photo_set(\d+)/:media_type/:media_id(\d+)/:slug?, /media/album/:photo_album(\d+)/:media_type/:media_id(\d+)/:slug?, /photo/:photo_id(\d+)/:slug?, /photo/:photo_set(\d+)/:photo_id(\d+)/:slug?
 * chunkName: pages.photo
 * bundle: web
 */
import {
  fetchDetail,
  useGlobal,
  useResourceAction,
  useAbortControl,
  useLocation
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import { APP_PHOTO, RESOURCE_PHOTO } from '@metafox/photo/constant';
import { get } from 'lodash';
import * as React from 'react';
import qs from 'query-string';

export default function HomePage(props) {
  const { createPageParams, dispatch, jsxBackend } = useGlobal();

  const [err, setErr] = React.useState<number>(0);
  const [loading, setLoading] = React.useState(true);
  const onError = React.useCallback((error: any) => {
    // eslint-disable-next-line no-console
    setErr(error);
    setLoading(false);
  }, []);
  const onSuccess = React.useCallback(() => {
    setLoading(false);
  }, []);
  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};
  const pageParams = createPageParams<{
    appName: string;
    resourceName: string;
    photo_id: string | number;
    photo_album?: string | number;
    photo_set?: string | number;
  }>(
    props,
    prev => ({
      photo_id: prev['photo_id'] || prev['media_id'],
      media_type: prev['media_type'] || APP_PHOTO
    }),
    prev => ({
      appName: prev['media_type'],
      resourceName: prev['media_type'],
      tab: 'landing',
      pageMetaName: `${prev['media_type']}.${RESOURCE_PHOTO}.landing`,
      identity: `${prev['media_type']}.entities.${prev['media_type']}.${prev['photo_id']}`,
      _pageType: 'viewItem'
    })
  );

  const { photo_set, photo_id, appName, resourceName, photo_album } =
    pageParams;
  const config = useResourceAction(appName, resourceName, 'viewItem');
  const abortId = useAbortControl(`${photo_id}`);

  React.useEffect(() => {
    if (photo_set) {
      dispatch({
        type: 'photo/photo_set/LOAD',
        payload: {
          photo_set,
          media_id: photo_id,
          onSuccess,
          onError
        },
        meta: {
          onSuccess,
          onError
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photo_set]);

  React.useEffect(() => {
    if (photo_album) {
      dispatch({
        type: 'photo/photo_album/LOAD',
        payload: {
          photo_album,
          media_id: photo_id
        },
        meta: {
          onSuccess,
          onError
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photo_album]);
  React.useEffect(() => {
    if (!config || photo_album || photo_set) return;

    if (photo_id) {
      // dispatch here on check error page
      dispatch(
        fetchDetail(
          config?.apiUrl,
          { id: pageParams?.photo_id, apiParams: { ...searchParams } },
          onSuccess,
          onError,
          abortId
        )
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photo_id, pageParams]);

  const errorPageParams = React.useMemo(() => {
    if (!err) return {};

    const message =
      get(err, 'response.data.error') || get(err, 'response.data.message');

    return { title: message, variant: 'h2' };
  }, [err]);

  if (err) {
    const pageName =
      get(err, 'response.status') === 403 ? 'core.error403' : 'core.error404';

    return <Page pageName={pageName} pageParams={errorPageParams} />;
  }

  if (loading) return jsxBackend.render({ component: 'Loading' });

  return <Page pageName={'photo.view'} pageParams={pageParams} />;
}
