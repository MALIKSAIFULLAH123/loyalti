/**
 * @type: route
 * name: user.verify_account
 * path: /verify-account
 * bundle: web
 */

import { useGlobal, useLocation, useResourceAction } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { APP_USER, RESOURCE_VERIFY } from '@metafox/user/constant';
import { compactData } from '@metafox/utils';
import * as React from 'react';

export default function RegisterPage(props) {
  const { createPageParams, createContentParams, usePageParams, redirectTo } =
    useGlobal();

  const location = useLocation() as any;
  const dataPayload = location?.state?.data;

  React.useEffect(() => {
    if (!dataPayload) {
      redirectTo('/');
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const params = usePageParams();

  const config = useResourceAction(APP_USER, RESOURCE_VERIFY, 'getVerifyForm');

  const pageParams = createPageParams(props, () => ({
    pageMetaName: 'user.verify_account',
    menuHeaderGuestLogin: 'hide'
  }));

  const contentParams = createContentParams({
    mainForm: {
      noBreadcrumb: true,
      dataSource: {
        apiMethod: config?.apiMethod,
        apiUrl: config?.apiUrl,
        apiRules: config?.apiRules,
        apiParams: compactData(config?.apiParams, { ...params, ...dataPayload })
      }
    }
  });

  return (
    <Page
      pageName="user.verify_account"
      contentParams={contentParams}
      pageParams={pageParams}
    />
  );
}
