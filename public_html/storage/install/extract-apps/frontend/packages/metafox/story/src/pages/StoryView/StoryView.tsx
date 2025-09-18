/**
 * @type: route
 * name: story.view
 * path: /story/:user_id(\d+)/:id(\d+)/:slug?
 * bundle: web
 */

import {
  fetchDetail,
  useGlobal,
  useLocation,
  useResourceAction
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import { get } from 'lodash';
import React, { createElement, useEffect } from 'react';
import qs from 'query-string';

export default function LandingPage(props) {
  const appName = 'story';
  const pageName = 'story.view';
  const resourceName = 'story';
  const loginRequired = true;
  const defaultTab = 'landing';

  const { createPageParams, dispatch, useSessionSummary, jsxBackend } =
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

  const session = useSessionSummary();
  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const config = useResourceAction(appName, resourceName, 'viewItem');

  const pageParams = createPageParams(props, (prev: any) => {
    return {
      appName,
      resourceName,
      tab: defaultTab,
      pageMetaName: `${appName}.${resourceName}.${defaultTab}`,
      authId: session?.user?.id,
      _pageType: 'browseItem',
      ...(prev?.id && {
        id: prev?.id,
        identity: `${appName}.entities.${resourceName}.${prev?.id}`
      }),
      ...(prev?.user_id && {
        user_id: prev?.user_id
      }),
      ...(prev?.tab && {
        tab: prev?.tab
      })
    };
  });

  useEffect(() => {
    if (config && pageParams?.id) {
      // dispatch here on check error page
      dispatch(
        fetchDetail(
          config.apiUrl,
          {
            apiParams: {
              ...searchParams,
              ...(config.apiParams || {})
            },
            pageParams
          },
          onSuccess,
          onFailure
        )
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [config, pageParams]);

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

  return createElement(Page, {
    pageName,
    pageParams,
    loginRequired
  });
}
