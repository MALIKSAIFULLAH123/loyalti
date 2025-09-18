import {
  usePublicSettings,
  useRoomItem,
  useSessionUser,
  useSubscriptionItem
} from '@metafox/chatplus/hooks';
import { useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React, { useCallback } from 'react';
import Messages from '../Messages';
import { LIMIT_SEARCH_MESSAGE } from '@metafox/chatplus/constants';

const name = 'ChatRoomPanel';

const LoadingStyled = styled('div', { name })(({ theme }) => ({
  textAlign: 'center',
  padding: theme.spacing(2),
  color:
    theme.palette.mode === 'dark'
      ? theme.palette.text.primary
      : theme.palette.grey['700'],
  fontSize: theme.spacing(1.75)
}));

const NoItemFound = styled('div', {
  name,
  slot: 'noItemFound'
})(({ theme }) => ({
  ...theme.typography.body1,
  padding: theme.spacing(1, 2),
  color: theme.palette.grey['600'],
  marginTop: theme.spacing(2)
}));

interface RefMessageHandle {
  scrollToBottom: () => void;
}

export interface Props {
  isPageFull?: boolean;
  [key: string]: any;
}

function SearchMessageBlock(props: Props, ref) {
  const {
    searchMessages,
    setLoadingMsgs,
    loadingMsgs,
    oldScrollOffset,
    chatRoom,
    handleAction,
    rid,
    isPageFull = true
  } = props;
  const { i18n, dispatch } = useGlobal();

  const scrollRef = React.useRef<HTMLDivElement>();
  const room = useRoomItem(rid);
  const subscription = useSubscriptionItem(rid);
  const { msgSearch } = chatRoom || {};

  const user = useSessionUser();
  const settings = usePublicSettings();

  const refMessage = React.useRef<RefMessageHandle>();

  const archived = !!subscription?.archived;

  const loadMore = useCallback(
    ({ type = 'lt' }) => {
      setLoadingMsgs(true);
      dispatch({
        type: 'chatplus/room/search/loadHistory',
        payload: {
          roomId: rid,
          mid: type === 'lt' ? searchMessages?.oldest : searchMessages?.newest,
          operate: type,
          limit: LIMIT_SEARCH_MESSAGE
        },
        meta: {
          onSuccess: () => {
            setLoadingMsgs(false);
          }
        }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [rid, searchMessages?.oldest, searchMessages?.newest]
  );

  const handleScroll = evt => {
    if (isEmpty(searchMessages) || msgSearch?.loading) return;

    const {
      endLoadmoreMessage,
      endTopLoadmoreMessage = false,
      oldest,
      newest
    } = searchMessages || {};

    if (evt.target.scrollTop < 100 && !loadingMsgs) {
      if (!oldest || endTopLoadmoreMessage) return;

      oldScrollOffset.current =
        evt.target.scrollHeight - evt.target.clientHeight;

      loadMore({ type: 'lt' });

      return;
    }

    if (
      evt.target.scrollHeight - evt.target.scrollTop - 50 <=
        evt.target.clientHeight &&
      !loadingMsgs
    ) {
      if (!newest || endLoadmoreMessage) return;

      oldScrollOffset.current =
        evt.target.scrollHeight - evt.target.clientHeight;

      loadMore({ type: 'gt' });
    }
  };

  React.useImperativeHandle(ref, () => {
    return {
      handleScroll: evt => {
        handleScroll(evt);
      }
    };
  });

  if (chatRoom?.searching) {
    if (isEmpty(searchMessages))
      return (
        <NoItemFound>
          {i18n.formatMessage({ id: 'no_results_found' })}
        </NoItemFound>
      );

    return (
      <Box sx={{ mt: isPageFull ? 2 : 0 }}>
        {loadingMsgs ? (
          <LoadingStyled>
            {i18n.formatMessage({ id: 'loading_dots' })}
          </LoadingStyled>
        ) : null}
        <Messages
          rid={rid}
          typing={null}
          groupIds={searchMessages?.groupIds}
          groups={searchMessages?.groups}
          newest={0}
          archived={archived}
          room={room}
          subscription={subscription}
          isMobile={false}
          settings={settings}
          user={user}
          containerRef={scrollRef}
          disableReact
          showToolbar={false}
          handleAction={handleAction}
          isAllPage
          ref={refMessage}
          chatRoom={chatRoom}
          isSearch
        />
        {loadingMsgs ? (
          <LoadingStyled>
            {i18n.formatMessage({ id: 'loading_dots' })}
          </LoadingStyled>
        ) : null}
      </Box>
    );
  }

  if (isEmpty(searchMessages)) return null;

  const disableReact = msgSearch?.mode === 'quote' ? false : true;

  const showToolbar = msgSearch?.mode === 'quote' ? true : false;

  return (
    <>
      {loadingMsgs ? (
        <LoadingStyled>
          {i18n.formatMessage({ id: 'loading_dots' })}
        </LoadingStyled>
      ) : null}
      <Messages
        rid={rid}
        typing={null}
        groupIds={searchMessages?.groupIds}
        groups={searchMessages?.groups}
        newest={0}
        archived={archived}
        room={room}
        subscription={subscription}
        isMobile={false}
        settings={settings}
        user={user}
        containerRef={scrollRef}
        disableReact={disableReact}
        showToolbar={showToolbar}
        handleAction={handleAction}
        isAllPage
        ref={refMessage}
        chatRoom={chatRoom}
        isSearch
      />
      {loadingMsgs ? (
        <LoadingStyled>
          {i18n.formatMessage({ id: 'loading_dots' })}
        </LoadingStyled>
      ) : null}
    </>
  );
}

export default React.forwardRef(SearchMessageBlock);
