/**
 * @type: route
 * name: user.verification
 * path: /user/verify/:hash
 * bundle: web
 */

import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

// support pageParams to control returnUrl
const EmailVerificationPage = props => {
  const { createPageParams } = useGlobal();

  const pageParams = createPageParams<{ name: string; hash: string }>(
    props,
    () => ({
      pageMetaName: 'user.user.verify',
      menuHeaderGuestLogin: 'hide'
    })
  );

  return <Page pageName="user.verification" pageParams={pageParams} />;
};

export default EmailVerificationPage;
