/**
 * @type: route
 * name: chat.home
 * path: /messages
 * chunkName: pages.chat
 */

import { useRooms } from '@metafox/chat/hooks';
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import React from 'react';

export default function HomePage(props) {
  const { createPageParams, dispatch, navigate, useIsMobile } = useGlobal();
  const isMobile = useIsMobile(true);
  const rooms = useRooms();

  const params = createPageParams<{
    noConversation: boolean;
  }>(props, prev => ({
    ...prev,
    appName: 'chat',
    pageMetaName: 'chat.messages.landing'
  }));

  const pageHelmet = {
    title: 'Chat Page'
  };

  React.useEffect(() => {
    if (rooms) {
      dispatch({
        type: 'chat/getFirstRoom',
        meta: {
          onSuccess: (value: any) => {
            if (value.id && !isMobile) {
              navigate(`/messages/${value.id}`, { replace: true });

              return null;
            }
          }
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rooms]);

  return (
    <Page
      pageName="chat.home"
      pageHelmet={pageHelmet}
      loginRequired
      pageParams={params}
    />
  );
}
