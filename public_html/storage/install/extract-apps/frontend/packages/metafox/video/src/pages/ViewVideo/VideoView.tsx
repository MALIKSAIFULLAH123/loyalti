/**
 * @type: route
 * name: video.view
 * path: /video/play/:id/:slug?, /media/:photo_set(\d+)/video/:id/:slug?, /media/album/:photo_album(\d+)/video/:id/:slug?
 * chunkName: pages.video
 * bundle: web
 */

import { fetchDetail, useGlobal, useLocation } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { APP_VIDEO, RESOURCE_VIDEO } from '@metafox/video/constant';
import { get } from 'lodash';
import React from 'react';
import qs from 'query-string';

export default function HomePage(props) {
  const { createPageParams, createContentParams, dispatch, jsxBackend } =
    useGlobal();
  const [err, setErr] = React.useState<number>(0);
  const [loading, setLoading] = React.useState(true);
  const onFailure = React.useCallback((error: any) => {
    // eslint-disable-next-line no-console
    setErr(error);
    setLoading(false);
  }, []);
  const onSuccess = React.useCallback(() => {
    setLoading(false);
  }, []);

  const location = useLocation();
  const isModal = location?.state?.asModal;
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const pageParams = createPageParams<{
    appName: string;
    resourceName: string;
    id: string | number;
  }>(props, prev => ({
    appName: APP_VIDEO,
    resourceName: RESOURCE_VIDEO,
    tab: 'landing',
    pageMetaName: `${APP_VIDEO}.${RESOURCE_VIDEO}.landing`,
    identity: `${APP_VIDEO}.entities.${RESOURCE_VIDEO}.${prev['id']}`,
    _pageType: 'viewItem'
  }));

  const contentParams = createContentParams({
    mainListing: {
      canLoadMore: true,
      contentType: RESOURCE_VIDEO,
      title: pageParams?.heading
    }
  });

  React.useEffect(() => {
    if (pageParams?.id && !isModal) {
      // dispatch here on check error page
      setLoading(true);
      dispatch(
        fetchDetail(
          '/video/:id',
          { id: pageParams?.id, apiParams: { ...searchParams } },
          onSuccess,
          onFailure
        )
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams?.id, pageParams]);

  React.useEffect(() => {
    if (err || loading) return;

    dispatch({
      type: `createViewItemPage/${RESOURCE_VIDEO}`,
      payload: { identity: pageParams.identity }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loading, err, pageParams.identity]);

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

  return (
    <Page
      pageName={'video.view'}
      pageParams={pageParams}
      contentParams={contentParams}
    />
  );
}
