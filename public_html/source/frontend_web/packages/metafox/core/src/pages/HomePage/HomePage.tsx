/**
 * @type: route
 * name: core.home
 * path: /, /home
 * chunkName: boot
 * bundle: web
 */
import { APP_FEED } from '@metafox/feed';
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';

export default function HomePage() {
  const { createPageParams, useLoggedIn, getSetting } = useGlobal();
  const isLogged = useLoggedIn();

  const loginRequired = getSetting<boolean>(
    'core.homepage_login_required',
    true
  );

  const pageParams = createPageParams({}, () => ({
    module_name: APP_FEED,
    item_type: APP_FEED,
    appName: APP_FEED,
    pageMetaName: isLogged ? `activity.${APP_FEED}.home` : 'home.guest'
  }));

  return (
    <Page
      pageName="home.member"
      pageParams={pageParams}
      loginRequired={loginRequired}
    />
  );
}
