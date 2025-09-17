/**
 * @type: route
 * name: feed.manageHidden
 * path: /manage-hidden
 * chunkName: pages.feed
 * bundle: web
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

export default function ManageHiddenPage(props: any) {
  const { createPageParams } = useGlobal();
  const pageParams = createPageParams<{ appName: string }>(props, () => ({
    appName: 'feed',
    tab: 'home'
  }));

  const pageHelmet = {
    title: 'Manage Hidden'
  };

  return (
    <Page
      pageName="feed.manageHidden"
      pageParams={pageParams}
      pageHelmet={pageHelmet}
    />
  );
}
