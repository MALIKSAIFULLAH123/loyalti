/**
 * @type: route
 * name: livestreaming.view
 * path: /live-video/:id(\d+)
 * chunkName: pages.livestreaming
 * bundle: web
 */

import { fetchDetail, useGlobal, useLocation } from '@metafox/framework';
import { Page } from '@metafox/layout';
import {
  APP_LIVESTREAM,
  RESOURCE_LIVE_VIDEO
} from '@metafox/livestreaming/constants';
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
  const pageAsModal = location?.state?.asModal;
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const pageParams = createPageParams<{
    appName: string;
    resourceName: string;
    id: string | number;
  }>(props, prev => ({
    appName: APP_LIVESTREAM,
    resourceName: RESOURCE_LIVE_VIDEO,
    tab: 'landing',
    pageMetaName: `${APP_LIVESTREAM}.${RESOURCE_LIVE_VIDEO}.landing`,
    _pageType: 'viewItem',
    identity: `${APP_LIVESTREAM}.entities.${RESOURCE_LIVE_VIDEO}.${prev['id']}`
  }));

  const contentParams = createContentParams({
    mainListing: {
      canLoadMore: true,
      contentType: RESOURCE_LIVE_VIDEO,
      title: pageParams?.heading
    }
  });

  React.useEffect(() => {
    if (pageParams?.id && !pageAsModal) {
      // dispatch here on check error page
      setLoading(true);
      dispatch(
        fetchDetail(
          '/live-video/:id',
          { id: pageParams?.id, apiParams: { ...searchParams } },
          onSuccess,
          onFailure
        )
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams?.id, pageParams]);

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
      pageName={'livestreaming.view'}
      pageParams={pageParams}
      contentParams={contentParams}
    />
  );
}
