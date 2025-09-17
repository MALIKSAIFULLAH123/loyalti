/**
 * @type: route
 * name: user.cancelAccount
 * path: /user/remove
 * bundle: web
 */
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { APP_USER } from '@metafox/user/constant';
import * as React from 'react';

export default function CancelAccountPage(props) {
  const {
    i18n,
    createPageParams,
    createContentParams,
    useSession,
    redirectTo,
    compactUrl
  } = useGlobal();
  const { loggedIn, user: authUser } = useSession();

  if (!loggedIn) {
    redirectTo('/');
  }

  const helmet = {
    title: i18n.formatMessage({ id: 'cancel_account' })
  };

  const config = useResourceAction(APP_USER, APP_USER, 'getCancelAccountForm');

  const pageParams = createPageParams(props, () => ({
    pageMetaName: 'user.user.cancel_account'
  }));

  const contentParams = createContentParams({
    mainForm: {
      noBreadcrumb: true,
      dataSource: {
        apiUrl: compactUrl(config?.apiUrl, { id: authUser?.id })
      }
    }
  });

  return (
    <Page
      pageName="user.cancel_account"
      pageHelmet={helmet}
      contentParams={contentParams}
      pageParams={pageParams}
    />
  );
}
