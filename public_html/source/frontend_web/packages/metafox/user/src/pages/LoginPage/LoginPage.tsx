/**
 * @type: route
 * name: user.login
 * path: /login
 * bundle: web
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';

// support pageParams to control returnUrl
const LoginPage = props => {
  const { createPageParams, useLoggedIn, redirectTo } = useGlobal();

  const pageParams = createPageParams(props, () => ({
    pageMetaName: 'user.user.login'
  }));

  const loggedIn = useLoggedIn();

  if (loggedIn) {
    redirectTo('/');

    return null;
  }

  return <Page pageName="user.login" pageParams={pageParams} />;
};

export default LoginPage;
