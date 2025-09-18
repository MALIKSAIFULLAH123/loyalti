import {
  useChatRoom,
  useChatUserItem,
  useIsSelfChat,
  useItemActionRoomDockChat,
  useMessageFilterItem,
  usePublicSettings,
  useRoomItem,
  useRoomPermission,
  useSessionUser,
  useSubscriptionItem
} from '@metafox/chatplus/hooks';
import { MsgItemShape, ReactMode, RoomType } from '@metafox/chatplus/types';
import { conversionStatusStr2Num } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { DropFileBox, LineIcon, TruncateText } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { styled, Box } from '@mui/material';
import { camelCase, isArray, isEmpty } from 'lodash';
import React, { useState } from 'react';
import {
  Panel,
  PanelFooter,
  PanelHeader,
  PanelTitle,
  PanelToolbar,
  SearchFilter
} from '../DockPanel';
import Messages from '../Messages';
import MessageFilter from '../MsgFilter/MessageFitler';
import FilesPreview from '../ChatComposer/FilePreview';
import { checkIsQuote } from '../MsgContent/MessageText';
import LatestMsgButton from '../Messages/LatestMsgButton';
import { formatGeneralMsg } from '@metafox/chatplus/services/formatTextMsg';
import SearchMessageBlock from '../ChatRoomPanel/SearchMessageBlock';
import { MODE_UN_SEARCH } from '@metafox/chatplus/constants';

const name = 'FlyChatRoomPanel';
const UIChatMsgStart = styled('div', { name, slot: 'UIChatMsgStart' })(
  ({ theme }) => ({
    textAlign: 'center',
    padding: theme.spacing(2, 2, 1),
    fontStyle: 'italic',
    color: theme.palette.text.primary,
    fontSize: theme.spacing(1.75)
  })
);
const ReplyEditWrapper = styled('div', { name, slot: 'ReplyEditWrapper' })(
  ({ theme }) => ({
    padding: theme.spacing(0.625, 1.25),
    height: '50px',
    width: '100%',
    borderTop: theme.mixins.border('secondary'),
    display: 'flex',
    justifyContent: 'space-between',
    backgroundColor:
      theme.palette.mode === 'dark'
        ? theme.palette.grey['600']
        : theme.palette.grey['100']
  })
);
const SelectedMsg = styled(TruncateText, { name, slot: 'SelectedMsg' })(
  ({ theme }) => ({
    color: theme.palette.text.primary,
    a: {
      pointerEvents: 'none',
      color: theme.palette.text.primary
    }
  })
);
const LineIconClose = styled(LineIcon, { name, slot: 'LineIconClose' })(
  ({ theme }) => ({
    cursor: 'pointer',
    alignSelf: 'center'
  })
);
const SelectedMsgAttachment = styled('div', {
  name,
  slot: 'SelectedMsgAttachment'
})(({ theme }) => ({
  fontSize: theme.spacing(1.5),
  margin: theme.spacing(0.5, 0),
  '& .ico': {
    marginRight: theme.spacing(0.5)
  }
}));
const ContentWrapper = styled('div', {
  name,
  slot: 'ContentWrapper'
})(({ theme }) => ({
  overflow: 'hidden'
}));
const SearchWrapper = styled(SearchFilter, {
  name: 'SearchWrapper'
})(({ theme }) => ({
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

const DropFileWrapper = styled(DropFileBox, {
  name,
  slot: 'DropFileBox'
})<{ isMobile?: boolean; openInfo?: boolean }>(({ theme }) => ({
  position: 'relative',
  flex: 1,
  display: 'flex',
  flexDirection: 'column',
  overflow: 'hidden',
  height: '100%'
}));

const DropFileBackground = styled(Box, { name, slot: 'DropFileBackground' })(
  ({ theme }) => ({
    backgroundColor: theme.palette.background.secondary,
    width: '100%',
    height: '100%',
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    zIndex: '9999',
    opacity: 0.9,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  })
);

interface State {}

interface Props {
  rid: string;
  collapsed?: boolean;
  active?: boolean;
  textDefault?: string;
}

interface RefMessageHandle {
  scrollToBottom: () => void;
}

interface RefSearchMessageHandle {
  handleScroll: (evt: any) => void;
}

export default function FlyChatRoomPanel({ rid, active, textDefault }: Props) {
  const { i18n, useActionControl, dispatch, jsxBackend, getSetting } =
    useGlobal();
  const containerRef = React.useRef<HTMLDivElement>();
  const scrollRef = React.useRef<HTMLDivElement>();
  const ChatComposer = jsxBackend.get('ChatComposer');
  const chatRoom = useChatRoom(rid);
  const room = useRoomItem(rid);
  const subscription = useSubscriptionItem(rid);
  const messageFilter = useMessageFilterItem(rid);
  const user = useSessionUser();
  let userId = room?.userId;
  const isSelfChat = useIsSelfChat(rid);
  const previewRef = React.useRef<any>();
  const filesUploadRef = React.useRef<any>();
  const refMessage = React.useRef<RefMessageHandle>();
  const refSearchMessage = React.useRef<RefSearchMessageHandle>();

  if (!userId && room?.t === RoomType.Direct) {
    const dataFilter = room?.uids?.filter(u => u?.id !== user?._id);

    if (dataFilter?.length) {
      userId = dataFilter[0];
    }
  }

  let userChat = useChatUserItem(userId);

  if (room?.usersCount === 1) userChat = user;

  const statusTmp = room?.t === RoomType.Direct ? userChat?.status : null;
  const statusUser = conversionStatusStr2Num(statusTmp, userChat?.invisible);
  const [handleAction] = useActionControl<State, unknown>(rid, {});
  const settings = usePublicSettings();
  const perms = useRoomPermission(rid);

  const [reactMode, setReactMode] = useState<ReactMode>('no_react');
  const [selectedMsg, setSelectedMsg] = useState<MsgItemShape>();
  const [loadingMsgs, setLoadingMsgs] = React.useState(false);
  const [showLatestMsg, setShowLatestMsg] = React.useState(false);

  const handleMarkAsRead = React.useCallback(() => {
    if (subscription?.alert) {
      dispatch({
        type: 'chatplus/room/markAsRead',
        payload: { identity: rid }
      });
    }
  }, [subscription, rid, dispatch]);

  React.useEffect(() => {
    if (!rid) return;

    setShowLatestMsg(false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (!rid) return;

    dispatch({ type: 'chatplus/room/active', payload: { rid } });

    return () => {
      dispatch({ type: 'chatplus/room/inactive', payload: { rid } });
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const searchMessages = React.useMemo(() => {
    if (isEmpty(chatRoom?.msgSearch)) return null;

    if (chatRoom?.msgSearch?.mode === MODE_UN_SEARCH) return null;

    if (chatRoom?.msgSearch?.mQuoteId) {
      return chatRoom?.searchMessages[chatRoom?.msgSearch?.mQuoteId];
    }

    return chatRoom?.searchMessages[chatRoom?.msgSearch?.id];
  }, [chatRoom?.msgSearch, chatRoom?.searchMessages]);

  const showSearchBlock = React.useMemo(() => {
    return chatRoom?.searching || !isEmpty(searchMessages);
  }, [searchMessages, chatRoom?.searching]);

  const searching = chatRoom?.searching;

  const archived = !!subscription?.archived;
  const allowMsgNoOne = !!(subscription?.allowMessageFrom === 'noone');
  const newest = chatRoom?.newest;
  const roomProgress = chatRoom?.roomProgress;
  const chatTitle = subscription?.fname || room?.name || room?.fname;
  const typing = (room && room?.typing) || [];
  const isMuted =
    user &&
    room &&
    room.muted &&
    isArray(room.muted) &&
    !!room.muted.find(x => x === user.username);

  const isReadOnly =
    (room?.ro && room?.t !== RoomType.Direct && !perms?.postReadonly) ||
    !!archived;

  const isMetaFoxBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.metafoxBlocked || subscription?.metafoxBlocker)
  );

  const isNormalBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.blocked || subscription?.blocker)
  );
  const isBlocked = !!(isNormalBlocked || isMetaFoxBlocked);

  const isChatBot = room?.isBotRoom;

  const disableReact =
    isChatBot || allowMsgNoOne || isBlocked || !getSetting('preaction');

  const itemAction = useItemActionRoomDockChat();

  const items = React.useMemo(
    () =>
      filterShowWhen(itemAction, {
        room,
        subscription,
        groups: chatRoom?.groups,
        pinned: chatRoom?.pinned,
        favorite: subscription?.f,
        starred: chatRoom?.starred,
        searching: chatRoom?.searching,
        settings,
        perms,
        isSelfChat,
        isBlocked,
        isMetaFoxBlocked,
        allowMsgNoOne,
        isReadOnly
      }),
    [
      chatRoom,
      subscription,
      room,
      settings,
      perms,
      itemAction,
      isSelfChat,
      isBlocked,
      isMetaFoxBlocked,
      allowMsgNoOne,
      isReadOnly
    ]
  );

  const handleCustomAction = (types: string, payload?: any) => {
    if (!types) return;

    // convert types into Array
    const typeArray = types.split(/.,| /);

    typeArray.forEach(type => {
      switch (type) {
        case 'chatplus/replyMessage':
          setReactMode('reply');
          setSelectedMsg(payload);
          break;
        case 'chatplus/editMessage':
          setReactMode('edit');
          setSelectedMsg(payload);
          break;
        default:
          break;
      }
    });
  };

  const handleCloseReactNode = () => {
    setReactMode('no_react');
    setSelectedMsg(undefined);
  };

  const handleComposeSuccess = () => {
    handleCloseReactNode();

    if (!isEmpty(searchMessages)) {
      dispatch({
        type: 'chatplus/room/modeSearch',
        payload: { rid, mode: MODE_UN_SEARCH },
        meta: {
          onSuccess: () => {
            if (refMessage?.current) {
              setShowLatestMsg(false);
              refMessage.current.scrollToBottom();
            }
          }
        }
      });

      return;
    }

    if (refMessage?.current) {
      refMessage.current.scrollToBottom();
    }
  };

  const { isQuote } = checkIsQuote(selectedMsg?.attachments);

  const msgReactNode = React.useMemo(() => {
    return selectedMsg?.msg
      ? formatGeneralMsg(selectedMsg?.msg, { mentions: selectedMsg?.mentions })
      : '';
  }, [selectedMsg]);
  const oldScrollOffset = React.useRef();

  const handleScroll = evt => {
    if (searching && !isEmpty(messageFilter)) return;

    if (
      evt.target.scrollHeight -
        evt.target.scrollTop -
        evt.target.clientHeight * 2 >
      0
    ) {
      setShowLatestMsg(true);
    } else {
      setShowLatestMsg(false);
    }

    if (isEmpty(searchMessages)) {
      if (evt.target.scrollTop < 100 && !loadingMsgs) {
        const { oldest, endLoadmoreMessage } = chatRoom || {};

        if (!oldest || endLoadmoreMessage) return;

        oldScrollOffset.current =
          evt.target.scrollHeight - evt.target.clientHeight;
        setLoadingMsgs(true);
        dispatch({
          type: 'chatplus/room/loadHistory',
          payload: { rid, oldest },
          meta: {
            onSuccess: () => {
              setLoadingMsgs(false);
            }
          }
        });
      }

      return;
    }

    if (refSearchMessage?.current) {
      refSearchMessage.current.handleScroll(evt);
    }
  };

  React.useEffect(() => {
    // check when some msg pushed for SearchBlock
    if (!searchMessages?.oldest || !oldScrollOffset.current) return;

    const curScrollPos = 0;
    const newScroll =
      scrollRef.current.scrollHeight - scrollRef.current.clientHeight;

    scrollRef.current.scrollTop =
      curScrollPos + (newScroll - oldScrollOffset.current);
    oldScrollOffset.current = 0;
  }, [searchMessages?.oldest]);

  React.useEffect(() => {
    // check when some msg pushed
    if (!chatRoom?.oldest || !oldScrollOffset.current) return;

    const curScrollPos = 0;
    const newScroll =
      scrollRef.current.scrollHeight - scrollRef.current.clientHeight;

    scrollRef.current.scrollTop =
      curScrollPos + (newScroll - oldScrollOffset.current);
    oldScrollOffset.current = 0;
  }, [chatRoom?.oldest]);

  const handleDropFiles = files => {
    if (!files.length) return;

    if (previewRef && previewRef.current) {
      const initData = filesUploadRef.current?.getPreviewFiles() || [];

      filesUploadRef.current?.attachFiles([...initData, ...files]);
      previewRef.current?.attachFiles([...initData, ...files]);
    }
  };

  return (
    <Panel ref={containerRef}>
      <PanelHeader searching={searching}>
        <PanelTitle
          room={room}
          status={statusUser}
          user={userChat}
          isSelfChat={isSelfChat}
        >
          {chatTitle}
        </PanelTitle>
        <PanelToolbar
          items={items}
          handleAction={handleAction}
          variant="roomPanel"
        />
      </PanelHeader>
      <SearchWrapper
        hide={!searching}
        roomId={rid}
        searching={searching}
        chatRoom={chatRoom}
        isBuddy
      />
      <DropFileWrapper
        data-testid={camelCase('Chat Dock content')}
        onClick={handleMarkAsRead}
        onDrop={files => {
          handleDropFiles(files);
        }}
        render={({ canDrop, isOver }) => (
          <>
            {canDrop && (
              <DropFileBackground>
                <TruncateText variant="h5" lines={1}>
                  {i18n.formatMessage({ id: 'drop_files_here' })}
                </TruncateText>
              </DropFileBackground>
            )}
            <Box sx={{ flex: 1, minHeight: 0, position: 'relative' }}>
              <ScrollContainer
                autoHide={false}
                autoHeight
                autoHeightMax={'100%'}
                ref={scrollRef}
                onScroll={handleScroll}
              >
                {searching && !isEmpty(messageFilter) ? (
                  <MessageFilter
                    user={user}
                    items={messageFilter}
                    archived={archived}
                    room={room}
                    isMobile={false}
                    settings={settings}
                    perms={perms}
                    disableReact={disableReact}
                    type={chatRoom?.pinned ? 'pin' : 'star'}
                    subscription={subscription}
                    ref={refMessage}
                  />
                ) : showSearchBlock ? (
                  <SearchMessageBlock
                    setReactMode={setReactMode}
                    setSelectedMsg={setSelectedMsg}
                    searchMessages={searchMessages}
                    loadingMsgs={loadingMsgs}
                    setLoadingMsgs={setLoadingMsgs}
                    oldScrollOffset={oldScrollOffset}
                    ref={refSearchMessage}
                    chatRoom={chatRoom}
                    rid={rid}
                    handleAction={handleCustomAction}
                    isPageFull={false}
                  />
                ) : (
                  <>
                    {searching || !chatRoom?.endLoadmoreMessage ? null : (
                      <UIChatMsgStart
                        data-testid={camelCase('start of conversation')}
                      >
                        {i18n.formatMessage({ id: 'start_of_conversation' })}
                      </UIChatMsgStart>
                    )}
                    <Messages
                      rid={rid}
                      typing={typing}
                      groupIds={chatRoom?.groupIds}
                      groups={chatRoom?.groups}
                      newest={newest}
                      archived={archived}
                      roomProgress={roomProgress}
                      room={room}
                      subscription={subscription}
                      isMobile={false}
                      settings={settings}
                      perms={{}}
                      user={user}
                      containerRef={scrollRef}
                      disableReact={disableReact}
                      handleAction={handleCustomAction}
                      ref={refMessage}
                    />
                  </>
                )}
              </ScrollContainer>
              {showLatestMsg && !searching ? (
                <LatestMsgButton
                  reactMode={reactMode}
                  refMessage={refMessage}
                  setShowLatestMsg={setShowLatestMsg}
                  searchMessages={searchMessages}
                  rid={rid}
                  showText={false}
                />
              ) : null}
            </Box>
            {reactMode !== 'no_react' && (
              <ReplyEditWrapper>
                <ContentWrapper>
                  <div>
                    {reactMode === 'reply'
                      ? i18n.formatMessage(
                          { id: 'chatplus_reply_to_user' },
                          {
                            user_name: selectedMsg?.u?.name,
                            is_owner: user?._id === selectedMsg?.u?._id ? 1 : 0
                          }
                        )
                      : i18n.formatMessage({ id: 'editing' })}
                  </div>
                  {!isEmpty(selectedMsg?.attachments) ? (
                    isQuote && !isEmpty(msgReactNode) ? (
                      <SelectedMsg
                        lines={1}
                        dangerouslySetInnerHTML={{ __html: msgReactNode }}
                      />
                    ) : (
                      <SelectedMsgAttachment>
                        <LineIcon icon="ico-paperclip-alt" />
                        <span>
                          {i18n.formatMessage({ id: 'file_attachment' })}{' '}
                        </span>
                      </SelectedMsgAttachment>
                    )
                  ) : (
                    <SelectedMsg
                      lines={1}
                      dangerouslySetInnerHTML={{ __html: msgReactNode }}
                    />
                  )}
                </ContentWrapper>
                <LineIconClose
                  icon="ico-close"
                  onClick={handleCloseReactNode}
                />
              </ReplyEditWrapper>
            )}
            <FilesPreview ref={previewRef} filesUploadRef={filesUploadRef} />
            <PanelFooter
              archived={archived}
              isReadOnly={isReadOnly}
              isMuted={isMuted}
              searching={searching}
              isBlocked={isBlocked}
              reactMode={reactMode}
              allowMsgNoOne={allowMsgNoOne}
            >
              <ChatComposer
                rid={rid}
                room={room}
                msgId={selectedMsg?.id}
                reactMode={reactMode}
                user={user}
                text={reactMode === 'edit' ? msgReactNode : textDefault || ''}
                onSuccess={handleComposeSuccess}
                subscription={subscription}
                previewRef={previewRef}
                ref={filesUploadRef}
              />
            </PanelFooter>
          </>
        )}
      />
    </Panel>
  );
}
