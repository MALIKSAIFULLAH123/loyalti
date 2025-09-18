/**
 * @type: route
 * name: chat.conversation
 * path: /messages/:rid(\d+)
 * chunkName: pages.chat
 */

import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

export default function ChatplusConversation(props) {
  const { createPageParams } = useGlobal();

  const params = createPageParams<{
    rid: string;
  }>(props, prev => ({
    ...prev,
    appName: 'chat',
    pageMetaName: 'chat.messages.landing',
    isAllPageMessages: true
  }));

  const pageHelmet = {
    title: 'Chat conversation'
  };

  // if (!setting) return <Page pageName="core.error404" />;

  return (
    <Page
      pageName="chat.conversation"
      pageHelmet={pageHelmet}
      loginRequired
      pageParams={params}
    />
  );
}
