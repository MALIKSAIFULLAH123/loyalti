/**
 * @type: route
 * name: chatplus.callview
 * path: /chatplus/call/:callId
 * chunkName: pages.chatplus
 * bundle: web
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

export default function CallView(props) {
  const { createPageParams } = useGlobal();

  const params = createPageParams<{
    callId: string;
  }>(props, prev => prev);

  const pageHelmet = {
    title: 'CallView'
  };

  return (
    <Page
      pageName="chatplus.callview"
      pageHelmet={pageHelmet}
      loginRequired
      pageParams={params}
    />
  );
}
