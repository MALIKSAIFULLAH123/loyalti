/**
 * @type: route
 * name: user.password
 * path: /user/password/:tab(request|request-method|verify-request|reset|logout-all)
 * chunkName: pages.user
 * bundle: web
 */
import {
  LOGGED_OUT,
  useGlobal,
  useResourceAction,
  useSession
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';
import { camelCase } from 'lodash';

export default function ForgotPasswordPage(props: any) {
  const { createContentParams, createPageParams, dispatch, redirectTo } =
    useGlobal();

  const { loggedIn } = useSession();

  if (loggedIn) {
    redirectTo('/');
  }

  dispatch({ type: LOGGED_OUT });

  const pageParams = createPageParams(props, prev => ({
    pageMetaName: `user.user.forgot_password.${prev.tab}`
  }));
  const dataSource = useResourceAction(
    'user',
    'user',
    camelCase(`getPassword_${pageParams?.tab}_Form`)
  );

  const contentParams = createContentParams({
    mainForm: {
      noBreadcrumb: true,
      noHeader: false,
      dataSource
    }
  });

  return (
    <Page
      pageName="user.password"
      contentParams={contentParams}
      pageParams={pageParams}
    />
  );
}
