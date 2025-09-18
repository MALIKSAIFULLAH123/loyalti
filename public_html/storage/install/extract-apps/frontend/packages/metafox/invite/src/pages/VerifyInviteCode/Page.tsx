/**
 * @type: route
 * name: invite.verify_invite_code
 * path: /invite/verify/:slug?
 * chunkName: pages.user
 * bundle: web
 */
import { useGlobal, useLocation, useResourceAction } from '@metafox/framework';
import { Page } from '@metafox/layout';
import * as React from 'react';
import qs from 'query-string';

export default function VerifyCorePage(props: any) {
  const { createContentParams, createPageParams, redirectTo } = useGlobal();

  const location = useLocation();

  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const dataSource: any =
    useResourceAction('socialite', 'socialite_invite', 'getVerifyInviteForm') ||
    {};

  if (!dataSource) {
    redirectTo('/');

    return;
  }

  const pageParams = createPageParams(props, prev => ({
    pageMetaName: 'invite.invite.verify_invite_code',
    menuHeaderGuestLogin: true
  }));

  const contentParams = createContentParams({
    mainForm: {
      noBreadcrumb: true,
      noHeader: false,
      dataSource: {
        ...dataSource,
        apiParams: searchParams
      }
    }
  });

  return (
    <Page
      pageName="invite.verify_invite_code"
      contentParams={contentParams}
      pageParams={pageParams}
    />
  );
}
