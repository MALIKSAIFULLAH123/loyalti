/**
 * @type: route
 * name: chat.conversation
 * path: /messages/:rid
 * chunkName: pages.chatplus
 * bundle: web
 */
import { useSessionUser } from '@metafox/chatplus/hooks';
import { ChatplusConfig, RoomItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { isEmpty } from 'lodash';
import React from 'react';

export default function ChatplusConversation(props) {
  const { createPageParams, getSetting, jsxBackend, dispatch, useLoggedIn } =
    useGlobal();
  const isLogged = useLoggedIn();
  const setting = getSetting<ChatplusConfig>('chatplus');
  const [loading, setLoading] = React.useState<boolean>(true);
  const [roomItem, setRoomItem] = React.useState<RoomItemShape>(null);

  const userSession = useSessionUser();

  React.useEffect(() => {
    if (!isEmpty(userSession?._id)) {
      dispatch({
        type: 'chatplus/room/getRoom',
        payload: { rid: props?.rid },
        meta: {
          onSuccess: value => {
            setRoomItem(value);
            setLoading(false);
          }
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userSession, props?.rid]);

  const params = createPageParams<{
    rid: string;
  }>(props, prev => ({
    ...prev,
    appName: 'chatplus',
    pageMetaName: 'chatplus.messages.landing',
    isAllPageMessages: true
  }));

  const pageHelmet = {
    title: 'Chat conversation'
  };

  if (isLogged && loading) return jsxBackend.render({ component: 'Loading' });

  if (isLogged && (!setting || !setting.server || !roomItem?._id))
    return <Page pageName="core.error404" />;

  return (
    <Page
      pageName="chatplus.conversation"
      pageHelmet={pageHelmet}
      loginRequired
      pageParams={params}
    />
  );
}
