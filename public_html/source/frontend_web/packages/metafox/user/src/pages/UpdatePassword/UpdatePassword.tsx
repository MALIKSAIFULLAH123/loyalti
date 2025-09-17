/**
 * @type: route
 * name: user.update_password
 * path: /user/password/update/:slug?
 * chunkName: pages.user
 * bundle: web
 */
import {
  useGlobal,
  useLocation,
  useResourceAction,
  useSession
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';
import qs from 'query-string';

export default function UpdatePasswordPage(props: any) {
  const { createContentParams, createPageParams, redirectTo, dispatch } =
    useGlobal();

  const location = useLocation();

  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const { loggedIn } = useSession();

  const dataSource: any =
    useResourceAction('user', 'user', 'editAccountPassword') || {};

  if (!loggedIn || !dataSource) {
    redirectTo('/');

    return;
  }

  const pageParams = createPageParams(props, prev => ({
    pageMetaName: 'user.user.update_password'
  }));

  const contentParams = createContentParams({
    mainForm: {
      noBreadcrumb: true,
      noHeader: false,
      dataSource: {
        ...dataSource,
        apiParams: searchParams
      },
      onCancel: () => {
        dispatch({ type: '@redirectAfterLogin' });
      }
    }
  });

  return (
    <Page
      pageName="user.update_password"
      contentParams={contentParams}
      pageParams={pageParams}
    />
  );
}
