/**
 * @type: route
 * name: user.register
 * path: /register
 * bundle: web
 */
import {
  LOGGED_OUT,
  useGlobal,
  useResourceAction,
  LAYOUT_EDITOR_TOGGLE
} from '@metafox/framework';
import { Page } from '@metafox/layout';
import { APP_USER } from '@metafox/user/constant';
import { compactData } from '@metafox/utils';
import * as React from 'react';

export default function RegisterPage(props) {
  const {
    i18n,
    createPageParams,
    dispatch,
    createContentParams,
    getSetting,
    redirectTo,
    useSession,
    usePageParams,
    localStore
  } = useGlobal();
  const canRegister = getSetting('user.allow_user_registration');
  const { loggedIn } = useSession();
  const params = usePageParams();
  const isEditMode = loggedIn && localStore.get(LAYOUT_EDITOR_TOGGLE);

  if ((!canRegister || loggedIn) && !isEditMode) {
    redirectTo('/');
  }

  const helmet = {
    title: i18n.formatMessage({ id: 'create_new_account' })
  };

  const config = useResourceAction(APP_USER, APP_USER, 'getRegisterForm');

  if (!isEditMode) {
    dispatch({ type: LOGGED_OUT });
  }

  const pageParams = createPageParams(props, () => ({
    pageMetaName: 'user.user.register',
    menuHeaderGuestLogin: true
  }));

  const contentParams = createContentParams({
    mainForm: {
      noBreadcrumb: true,
      dataSource: {
        apiUrl: config?.apiUrl,
        apiParams: compactData(config?.apiParams, params)
      }
    }
  });

  return (
    <Page
      pageName="user.register"
      pageHelmet={helmet}
      contentParams={contentParams}
      pageParams={pageParams}
    />
  );
}
