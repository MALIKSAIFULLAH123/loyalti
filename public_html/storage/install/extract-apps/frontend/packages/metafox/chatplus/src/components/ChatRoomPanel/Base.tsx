import {
  useChatRoom,
  useChatUserItem,
  useIsSelfChat,
  useMessageFilterItem,
  usePublicSettings,
  useRoomItem,
  useRoomPermission,
  useSessionUser,
  useSubscriptionItem
} from '@metafox/chatplus/hooks';
import { getBuddyItem } from '@metafox/chatplus/selectors';
import { MsgItemShape, ReactMode, RoomType } from '@metafox/chatplus/types';
import { BlockViewProps, GlobalState, useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { DropFileBox, LineIcon, TruncateText } from '@metafox/ui';
import { styled, useTheme, Box } from '@mui/material';
import { camelCase, isArray, isEmpty } from 'lodash';
import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import { PanelFooter } from '../DockPanel';
import Messages from '../Messages';
import HeaderRoom from './Header/Header';
import NoConversation from './NoConversation';
import SideInfo from './SideInfo/SideInfo';
import FilesPreview from '../ChatComposer/FilePreview';
import { checkIsQuote } from '../MsgContent/MessageText';
import { formatGeneralMsg } from '@metafox/chatplus/services/formatTextMsg';
import LatestMsgButton from '../Messages/LatestMsgButton';
import SearchMessageBlock from './SearchMessageBlock';
import { MODE_UN_SEARCH } from '@metafox/chatplus/constants';
import MessageFilter from '../MsgFilter/MessageFitler';

const name = 'ChatRoomPanel';

const Root = styled('div')(({ theme }) => ({
  borderLeft: theme.mixins.border('divider'),
  backgroundColor: theme.palette.background.paper,
  display: 'flex',
  flexDirection: 'row',
  width: '100%',
  height: '100%'
}));

const MainContent = styled('div', {
  name,
  slot: 'mainContent',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  flex: 1,
  display: 'flex',
  width: '100%',
  height: '100%',
  ...(isMobile && {
    flexDirection: 'column'
  })
}));
const Body = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  flex: 1,
  width: '100%',
  minHeight: 0
}));

const Main = styled('div', {
  name,
  slot: 'Main',
  shouldForwardProp: props => props !== 'isMobile' && props !== 'openInfo'
})<{ isMobile?: boolean; openInfo?: boolean }>(
  ({ theme, isMobile, openInfo }) => ({
    position: 'relative',
    flex: 1,
    display: 'flex',
    flexDirection: 'column',
    overflow: 'hidden',
    height: '100%',
    ...(isMobile &&
      openInfo && {
        flex: 0,
        display: 'none'
      })
  })
);

const DropFileWrapper = styled(DropFileBox, {
  name,
  slot: 'DropFileBox',
  shouldForwardProp: props => props !== 'isMobile' && props !== 'openInfo'
})<{ isMobile?: boolean; openInfo?: boolean }>(
  ({ theme, isMobile, openInfo }) => ({
    position: 'relative',
    flex: 1,
    display: 'flex',
    flexDirection: 'column',
    overflow: 'hidden',
    height: '100%',
    ...(isMobile &&
      openInfo && {
        flex: 0,
        display: 'none'
      })
  })
);

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

const SideInfoStyled = styled('div', {
  name,
  slot: 'sideInfoStyled',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  flexBasis: '360px',
  ...(isMobile && {
    flex: 1
  }),
  overflow: 'hidden'
}));

const UIChatMsgStart = styled('div')(({ theme }) => ({
  textAlign: 'center',
  padding: theme.spacing(2),
  fontStyle: 'italic',
  color:
    theme.palette.mode === 'dark'
      ? theme.palette.text.primary
      : theme.palette.grey['700'],
  fontSize: theme.spacing(1.75)
}));

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

interface RefMessageHandle {
  scrollToBottom: () => void;
}

interface RefSearchMessageHandle {
  handleScroll: (evt: any) => void;
}

export interface Props extends BlockViewProps {}

export default function Block(props: Props) {
  const { i18n, jsxBackend, usePageParams, dispatch, useIsMobile, getSetting } =
    useGlobal();

  const isMobile = useIsMobile(true);
  const isSmallScreen = useIsMobile();
  const pageParams = usePageParams();

  const { rid, noConversation } = pageParams;

  const scrollRef = React.useRef<HTMLDivElement>();
  const chatRoom = useChatRoom(rid);
  const room = useRoomItem(rid);
  const subscription = useSubscriptionItem(rid);
  const messageFilter = useMessageFilterItem(rid);

  const buddy = useSelector((state: GlobalState) => getBuddyItem(state, rid));
  const user = useSessionUser();
  const settings = usePublicSettings();
  const perms = useRoomPermission(rid);
  const userChat = useChatUserItem(room?.userId);
  const isSelfChat = useIsSelfChat(rid);

  const [openInfo, setOpenInfo] = React.useState<boolean>(false);
  const [reactMode, setReactMode] = useState<ReactMode>('no_react');
  const [selectedMsg, setSelectedMsg] = useState<MsgItemShape>();
  const [loadingMsgs, setLoadingMsgs] = React.useState(false);
  const [showLatestMsg, setShowLatestMsg] = React.useState(false);

  const theme = useTheme();

  const previewRef = React.useRef<any>();
  const filesUploadRef = React.useRef<any>();
  const refMessage = React.useRef<RefMessageHandle>();
  const refSearchMessage = React.useRef<RefSearchMessageHandle>();

  const ChatComposer = jsxBackend.get('ChatComposer');

  const oldScrollOffset = React.useRef<any>();

  const searchMessages = React.useMemo(() => {
    if (isEmpty(chatRoom?.msgSearch)) return null;

    if (chatRoom?.msgSearch?.mode === MODE_UN_SEARCH) return null;

    if (chatRoom?.msgSearch?.mQuoteId) {
      return chatRoom?.searchMessages[chatRoom?.msgSearch?.mQuoteId];
    }

    return chatRoom?.searchMessages[chatRoom?.msgSearch?.id];
  }, [chatRoom?.msgSearch, chatRoom?.searchMessages]);

  const showSearchBlock: boolean = React.useMemo(() => {
    return chatRoom?.searching || !isEmpty(searchMessages);
  }, [searchMessages, chatRoom?.searching]);

  const [scrolling, setScrolling] = useState(false);

  const onScrollStop = React.useCallback(() => {
    if (isSmallScreen) setScrolling(false);
  }, [isSmallScreen]);

  const handleScroll = evt => {
    if (isSmallScreen) setScrolling(true);

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

  const handleMarkAsRead = React.useCallback(() => {
    if (subscription?.alert) {
      dispatch({
        type: 'chatplus/room/markAsRead',
        payload: { identity: rid }
      });
    }
  }, [subscription, rid, dispatch]);

  const handleCloseReactNode = () => {
    setReactMode('no_react');
    setSelectedMsg(undefined);
  };

  React.useEffect(() => {
    if (rid) {
      dispatch({
        type: 'chatplus/room/markAsRead',
        payload: { identity: rid }
      });
      dispatch({
        type: 'chatplus/openRooms/activeRoom',
        payload: rid
      });
    }

    return () => {
      dispatch({
        type: 'chatplus/openRooms/activeRoom',
        payload: undefined
      });
      setOpenInfo(false);
      setShowLatestMsg(false);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid]);

  const isReadyRoom = !isEmpty(room && subscription && chatRoom && buddy);

  React.useEffect(() => {
    if (isReadyRoom) {
      dispatch({ type: 'chatplus/room/active', payload: { rid } });
    }

    return () => {
      dispatch({ type: 'chatplus/room/inactive', payload: { rid } });
      handleCloseReactNode();
      // filesUploadRef.current.clearEditor();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid, isReadyRoom]);

  const toggleInfo = React.useCallback(() => setOpenInfo(prev => !prev), []);

  const { isQuote } = checkIsQuote(selectedMsg?.attachments);

  const msgReactNode = React.useMemo(() => {
    return selectedMsg?.msg
      ? formatGeneralMsg(selectedMsg?.msg, { mentions: selectedMsg?.mentions })
      : '';
  }, [selectedMsg]);

  if (!rid && noConversation && !isMobile) return <NoConversation />;

  const archived = !!subscription?.archived;

  const isNormalBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.blocked || subscription?.blocker)
  );

  const isMetaFoxBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.metafoxBlocked || subscription?.metafoxBlocker)
  );

  const isBlocked = !!(isNormalBlocked || isMetaFoxBlocked);

  const allowMsgNoOne = !!(subscription?.allowMessageFrom === 'noone');

  const newest = chatRoom?.newest || 0;
  const roomProgress = chatRoom?.roomProgress;
  const typing = (room && room?.typing) || [];
  const disableReact = allowMsgNoOne || isBlocked || !getSetting('preaction');

  const isMuted =
    user &&
    room &&
    room.muted &&
    isArray(room.muted) &&
    !!room.muted.find(x => x === user.username);

  const isReadOnly =
    (room?.ro && room?.t !== RoomType.Direct && !perms?.postReadonly) ||
    !!archived;

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

  const styleScroll: React.CSSProperties = {
    backgroundColor: theme.palette.background.paper
  };

  const handleDropFiles = files => {
    if (!files.length) return;

    if (previewRef && previewRef.current) {
      const initData = filesUploadRef.current?.getPreviewFiles() || [];

      filesUploadRef.current?.attachFiles([...initData, ...files]);
      previewRef.current?.attachFiles([...initData, ...files]);
    }
  };

  return (
    <Root data-testid={camelCase('block chatroom')}>
      <MainContent isMobile={isMobile}>
        {isMobile ? (
          <HeaderRoom
            buddy={buddy}
            perms={perms}
            userChat={userChat}
            room={room}
            toggleInfo={toggleInfo}
            messageFilter={messageFilter}
            chatRoom={chatRoom}
            subscription={subscription}
            searching={chatRoom?.searching}
            settings={settings}
            isSelfChat={isSelfChat}
            allowMsgNoOne={allowMsgNoOne}
            isReadOnly={isReadOnly}
            isMetaFoxBlocked={isMetaFoxBlocked}
            isBlocked={isBlocked}
            openInfo={openInfo}
          />
        ) : null}
        <Body>
          <Main isMobile={isMobile} openInfo={openInfo}>
            {!isMobile ? (
              <HeaderRoom
                buddy={buddy}
                perms={perms}
                userChat={userChat}
                room={room}
                toggleInfo={toggleInfo}
                messageFilter={messageFilter}
                chatRoom={chatRoom}
                subscription={subscription}
                searching={chatRoom?.searching}
                settings={settings}
                isSelfChat={isSelfChat}
                allowMsgNoOne={allowMsgNoOne}
                isReadOnly={isReadOnly}
                isMetaFoxBlocked={isMetaFoxBlocked}
                isBlocked={isBlocked}
                openInfo={openInfo}
              />
            ) : null}
            <DropFileWrapper
              onClick={handleMarkAsRead}
              isMobile={isMobile}
              openInfo={openInfo}
              onDrop={files => {
                handleDropFiles(files);
              }}
              render={({ canDrop, isOver }) => (
                <>
                  {canDrop && isOver && (
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
                      style={styleScroll}
                      onScroll={handleScroll}
                      onScrollStop={onScrollStop}
                    >
                      {isMobile &&
                      chatRoom?.searching &&
                      !isEmpty(messageFilter) ? (
                        <MessageFilter
                          user={user}
                          items={messageFilter}
                          archived={archived}
                          room={room}
                          isMobile={isMobile}
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
                          isPageFull
                        />
                      ) : (
                        <>
                          {chatRoom?.searching ||
                          !chatRoom?.endLoadmoreMessage ? null : (
                            <UIChatMsgStart>
                              {i18n.formatMessage({
                                id: 'start_of_conversation'
                              })}
                            </UIChatMsgStart>
                          )}
                          <Messages
                            rid={rid}
                            typing={typing}
                            groupIds={chatRoom?.groupIds}
                            groups={chatRoom?.groups}
                            roomProgress={roomProgress}
                            newest={newest}
                            archived={archived}
                            room={room}
                            subscription={subscription}
                            isMobile={false}
                            settings={settings}
                            user={user}
                            containerRef={scrollRef}
                            disableReact={disableReact}
                            handleAction={handleCustomAction}
                            isAllPage
                            ref={refMessage}
                          />
                        </>
                      )}
                    </ScrollContainer>
                    {showLatestMsg && !scrolling && !chatRoom?.searching ? (
                      <LatestMsgButton
                        showText={!isSmallScreen}
                        reactMode={reactMode}
                        refMessage={refMessage}
                        setShowLatestMsg={setShowLatestMsg}
                        searchMessages={searchMessages}
                        rid={rid}
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
                                  is_owner:
                                    user?._id === selectedMsg?.u?._id ? 1 : 0
                                }
                              )
                            : i18n.formatMessage({ id: 'editing' })}
                        </div>
                        {!isEmpty(selectedMsg?.attachments) ? (
                          isQuote && !isEmpty(msgReactNode) ? (
                            <SelectedMsg
                              lines={1}
                              dangerouslySetInnerHTML={{
                                __html: msgReactNode
                              }}
                            />
                          ) : (
                            <SelectedMsgAttachment>
                              <LineIcon icon="ico-paperclip-alt" />
                              <span>
                                {i18n.formatMessage({
                                  id: 'file_attachment'
                                })}{' '}
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
                        onClick={() => setReactMode('no_react')}
                      />
                    </ReplyEditWrapper>
                  )}
                  <FilesPreview
                    ref={previewRef}
                    filesUploadRef={filesUploadRef}
                    isAllPage
                  />
                  <PanelFooter
                    archived={archived}
                    isReadOnly={isReadOnly}
                    isMuted={isMuted}
                    searching={chatRoom?.searching}
                    isBlocked={isBlocked}
                    reactMode={reactMode}
                    allowMsgNoOne={allowMsgNoOne}
                  >
                    <ChatComposer
                      rid={rid}
                      room={room}
                      user={user}
                      msgId={selectedMsg?.id}
                      reactMode={reactMode}
                      text={
                        reactMode === 'edit'
                          ? msgReactNode
                          : chatRoom?.textEditor || ''
                      }
                      onSuccess={handleComposeSuccess}
                      subscription={subscription}
                      previewRef={previewRef}
                      ref={filesUploadRef}
                      isAllPage
                    />
                  </PanelFooter>
                </>
              )}
            />
          </Main>
          {openInfo ? (
            <SideInfoStyled isMobile={isMobile}>
              <SideInfo toggleInfo={toggleInfo} buddy={buddy} room={room} />
            </SideInfoStyled>
          ) : null}
        </Body>
      </MainContent>
    </Root>
  );
}
