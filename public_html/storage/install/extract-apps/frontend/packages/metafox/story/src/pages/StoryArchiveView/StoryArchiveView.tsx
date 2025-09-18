/**
 * @type: route
 * name: story-archive.view
 * path: /story-archive/:user_id(\d+)/:id(\d+)/:slug?
 * bundle: web
 */

import { useGlobal, useLocation } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { get } from 'lodash';
import React, { createElement, useEffect } from 'react';
import qs from 'query-string';
import { STORY_CLEAR_PAGINATION_NOFULL } from '@metafox/story/constants';

export default function LandingPage(props) {
  const appName = 'story';
  const pageName = 'story-archive.view';
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
    if (pageParams?.id) {
      dispatch({
        type: 'story/story_archive/LOAD',
        payload: {
          story_id: pageParams?.id,
          user_id: pageParams?.user_id,
          date: searchParams?.date
        },
        meta: {
          onSuccess,
          onError: onFailure
        }
      });
    }

    return () => {
      dispatch({
        type: STORY_CLEAR_PAGINATION_NOFULL
      });
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams]);

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
