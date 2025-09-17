import { useGlobal, useLocation, useResourceActions } from '@metafox/framework';
import { Page } from '@metafox/layout';
import qs from 'query-string';
import { createElement, useEffect } from 'react';
import { PageCreatorConfig, PageParams } from '../types';

interface Params extends PageParams {
  id: string;
}

interface Config<T> extends PageCreatorConfig<T> {
  resourceName: string;
  changeEventName?: string;
  apiUrl?: string;
  breadcrumbs?: any;
  disableFormOnSuccess?: boolean;
  editActionName?: string;
  addActionName?: string;
  successAction?: string;
}

export default function createEditingPage<T extends Params = Params>({
  pageName,
  appName,
  resourceName,
  paramCreator,
  changeEventName,
  apiUrl: initialApiUrl,
  breadcrumbs,
  disableFormOnSuccess = false,
  loginRequired = true,
  editActionName = 'editItem',
  addActionName = 'addItem',
  successAction
}: Config<T>) {
  function EditingPage(props: any) {
    const { createPageParams, createContentParams, dispatch } = useGlobal();
    const pageParams: T = createPageParams<T>(
      props,
      ({ id }) => ({
        appName,
        resourceName,
        breadcrumb: true,
        _pageType: 'editItem',
        pageMetaName: `${appName}.${resourceName}.${id ? 'edit' : 'create'}`
      }),
      paramCreator
    );
    const location = useLocation();
    const searchParams = location?.search
      ? qs.parse(location.search.replace(/^\?/, ''))
      : {};

    const config = useResourceActions(appName, resourceName);

    useEffect(() => {
      dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pageParams]);

    if (!initialApiUrl && !config) {
      return createElement(Page, {
        pageName: 'core.error404'
      });
    }

    let apiUrl = initialApiUrl;

    if (!apiUrl)
      apiUrl = pageParams.id
        ? config?.[editActionName]?.apiUrl
        : config?.[addActionName]?.apiUrl;

    const contentParams = createContentParams({
      mainForm: {
        formName: `${appName}.${resourceName}`,
        breadcrumbs,
        disableFormOnSuccess,
        dataSource: {
          apiUrl,
          apiParams: { id: pageParams.id, ...searchParams }
        },
        changeEventName,
        successAction
      }
    });

    return createElement(Page, {
      pageName,
      pageParams,
      contentParams,
      loginRequired
    });
  }

  EditingPage.displayName = `EditingPage(${pageName})`;

  return EditingPage;
}
