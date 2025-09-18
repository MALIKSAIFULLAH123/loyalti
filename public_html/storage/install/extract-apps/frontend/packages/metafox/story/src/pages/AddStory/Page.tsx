/**
 * @type: route
 * name: story.add
 * path: /story/add
 * chunkName: pages.story
 * bundle: web
 */

import { useResourceActions, useGlobal, useLocation } from '@metafox/framework';
import { StoryPreviewChanged } from '@metafox/story/constants';
import { Page } from '@metafox/layout';
import React, { createElement, useEffect } from 'react';
import qs from 'query-string';

export default function EditingPage(props) {
  const appName = 'story';
  const resourceName = 'story';
  const pageName = 'story.add';
  const initialApiUrl = null;
  const disableFormOnSuccess = false;
  const loginRequired = true;

  const { createPageParams, createContentParams, dispatch, getAcl } =
    useGlobal();
  const createAclStory = getAcl('story.story.create');

  const pageParams: any = createPageParams(props, ({ id }) => ({
    appName,
    resourceName,
    _pageType: 'editItem',
    pageMetaName: `${appName}.${resourceName}.${id ? 'edit' : 'create'}`,
    pageTitle: 'stories',
    backPage: false,
    backPageProps: {
      title: 'stories',
      to: '/story'
    }
  }));
  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const config = useResourceActions(appName, resourceName);

  useEffect(() => {
    dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageParams]);

  if (!createAclStory) return <Page pageName="core.error403" />;

  if (!initialApiUrl && !config) {
    return createElement(Page, {
      pageName: 'core.error404'
    });
  }

  let apiUrl = initialApiUrl;

  if (!apiUrl)
    apiUrl = pageParams.id ? config.editItem?.apiUrl : config.addItem?.apiUrl;

  if (!apiUrl) apiUrl = config.editItem?.apiUrl;

  const contentParams = createContentParams({
    mainForm: {
      formName: `${appName}.${resourceName}`,
      disableFormOnSuccess,
      dataSource: {
        apiUrl,
        apiParams: { id: pageParams.id, ...searchParams }
      },
      changeEventName: StoryPreviewChanged
    }
  });

  return createElement(Page, {
    pageName,
    pageParams,
    contentParams,
    loginRequired
  });
}
