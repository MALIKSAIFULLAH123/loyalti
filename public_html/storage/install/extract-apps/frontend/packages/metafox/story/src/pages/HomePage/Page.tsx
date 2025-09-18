/**
 * @type: route
 * name: story.home
 * path: /story, /story/:user_story_id(\d+)
 * chunkName: pages.story
 * bundle: web
 */

import { useGlobal, useLocation } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { createElement, useEffect } from 'react';

export default function LandingPage(props) {
  const appName = 'story';
  const pageName = 'story.home';
  const resourceName = 'story';
  const loginRequired = true;
  const defaultTab = 'landing';

  const { createPageParams, dispatch, useSessionSummary } = useGlobal();

  const session = useSessionSummary();
  const location = useLocation();

  const pageParams = createPageParams(props, (prev: any) => {
    return {
      appName,
      resourceName,
      tab: defaultTab,
      pageMetaName: `${appName}.${resourceName}.${defaultTab}`,
      authId: session?.user?.id,
      _pageType: 'browseItem',
      related_user_id: prev?.user_story_id || location?.state?.related_user_id
    };
  });

  useEffect(() => {
    dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams?.related_user_id]);

  return createElement(Page, {
    pageName,
    pageParams,
    loginRequired
  });
}
