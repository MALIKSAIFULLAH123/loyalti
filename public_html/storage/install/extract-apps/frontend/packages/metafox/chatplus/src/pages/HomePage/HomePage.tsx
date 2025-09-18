/**
 * @type: route
 * name: chatplus.home
 * path: /messages
 * chunkName: pages.chatplus
 * bundle: web
 */
import { useSessionUser } from '@metafox/chatplus/hooks';
import { ChatplusConfig } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { isEmpty } from 'lodash';
import React from 'react';

export default function ChatPlusHomePage(props) {
  const {
    getSetting,
    navigate,
    createPageParams,
    useIsMobile,
    dispatch,
    jsxBackend,
    useLoggedIn
  } = useGlobal();
  const isLogged = useLoggedIn();
  const setting = getSetting<ChatplusConfig>('chatplus');
  const isMobile = useIsMobile(true);
  const [isConversation, setIsConversation] = React.useState(null);
  const [loading, setLoading] = React.useState<boolean>(true);

  const userSession = useSessionUser();

  React.useEffect(() => {
    if (!isEmpty(userSession?._id)) {
      dispatch({
        type: 'chatplus/room/getFirstRoom',
        meta: {
          onSuccess: value => {
            setIsConversation(value);
            setLoading(false);
          }
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userSession]);

  if (!setting || !setting.server) return <Page pageName="core.error404" />;

  const params = createPageParams<{
    noConversation: boolean;
  }>(props, prev => ({
    ...prev,
    appName: 'chatplus',
    pageMetaName: 'chatplus.messages.landing',
    noConversation: !isConversation,
    isAllPageMessages: true
  }));

  const pageHelmet = {
    title: 'Chat homePage'
  };

  if (isLogged && loading) return jsxBackend.render({ component: 'Loading' });

  if (isLogged && isConversation && !isMobile) {
    navigate(`/messages/${isConversation.id}`, { replace: true });

    return null;
  }

  return (
    <Page
      pageName="chatplus.home"
      pageHelmet={pageHelmet}
      loginRequired
      pageParams={params}
    />
  );
}
