import {
  Panel,
  PanelContent,
  PanelFooter,
  PanelHeader,
  PanelTitle,
  PanelToolbar,
  SearchFilter
} from '@metafox/chat/components/DockPanel';
import {
  useChatRoom,
  useItemActionDockChat,
  useRoomItem
} from '@metafox/chat/hooks';
import { formatGeneralMsg } from '@metafox/chat/services/formatTextMsg';
import { MsgItemShape, ReactMode } from '@metafox/chat/types';
import { useGlobal, useSession } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon, TruncateText } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { styled, Box, CircularProgress } from '@mui/material';
import { camelCase, isEmpty } from 'lodash';
import React, { useState } from 'react';
import Messages from '../Messages';
import MessageFilter from '../Messages/MessageFitler';
import FilesPreview from '../ChatComposer/FilePreview';
import SearchMessageBlock from '../ChatRoomPanel/SearchMessageBlock';
import { MODE_UN_SEARCH } from '@metafox/chat/constants';

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
    color: theme.palette.text.primary
  })
);
const LineIconClose = styled(LineIcon, { name, slot: 'LineIconClose' })(
  ({ theme }) => ({
    margin: theme.spacing(1),
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

const LoadingStyled = styled('div', { name, slot: 'LoadingStyled' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: theme.spacing(3)
  })
);

const SearchWrapper = styled(SearchFilter, {
  name: 'SearchWrapper'
})(({ theme }) => ({
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

interface State {}

interface Props {
  rid: string;
  collapsed?: boolean;
  active?: boolean;
}

interface RefMessageHandle {
  scrollToBottom: () => void;
}

interface RefSearchMessageHandle {
  handleScroll: (evt: any) => void;
}

export default function FlyChatRoomPanel({
  rid,
  collapsed,
  textDefault
}: Props) {
  const {
    i18n,
    useActionControl,
    dispatch,
    jsxBackend,
    useGetItem,
    getSetting,
    getAcl
  } = useGlobal();

  const acl: any = getAcl('chat.chat_message');
  const previewRef = React.useRef();
  const filesUploadRef = React.useRef();
  const scrollRef = React.useRef<HTMLDivElement>();
  const refMessage = React.useRef<RefMessageHandle>();
  const refSearchMessage = React.useRef<RefSearchMessageHandle>();

  const chatRoom = useChatRoom(rid);
  const room = useRoomItem(rid);

  const [handleAction] = useActionControl<State, unknown>(rid, {});

  const [reactMode, setReactMode] = useState<ReactMode>('no_react');
  const [selectedMsg, setSelectedMsg] = useState<MsgItemShape>();
  const [data, setData] = useState(undefined);
  const [loading, setLoading] = useState(false);

  const userIdentity = data?.other_members[0] || undefined;

  const user = useGetItem(userIdentity);
  const { user: authUser } = useSession();
  const userSelectedMsg = useGetItem(selectedMsg?.user);

  const ChatSimpleComposer = jsxBackend.get('ChatSimpleComposer');

  const [loadingMsgs, setLoadingMsgs] = React.useState(false);

  const oldScrollOffset = React.useRef();

  React.useEffect(() => {
    // check when some msg pushed
    if (!chatRoom?.lastMsgId || !oldScrollOffset.current) return;

    const curScrollPos = 0;
    const newScroll =
      scrollRef.current.scrollHeight - scrollRef.current.clientHeight;

    scrollRef.current.scrollTop =
      curScrollPos + (newScroll - oldScrollOffset.current);
    oldScrollOffset.current = 0;
  }, [chatRoom?.lastMsgId]);

  const handleMarkAsRead = React.useCallback(() => {
    if (rid) {
      dispatch({
        type: 'chat/room/markAsRead',
        payload: { identity: rid }
      });
    }
  }, [rid, dispatch]);

  React.useEffect(() => {
    if (rid) {
      dispatch({
        type: 'chat/room/markAsRead',
        payload: { identity: rid }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid]);

  React.useEffect(() => {
    if (!rid) return;

    setLoading(true);
    dispatch({
      type: 'chat/room/active',
      payload: rid,
      meta: {
        onSuccess: value => {
          setData(value);
          setLoading(false);
        },
        onFailure: () => {
          setLoading(false);
        }
      }
    });

    return () => {
      dispatch({ type: 'chat/room/inactive', payload: { rid } });
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const searchMessages = React.useMemo(() => {
    if (isEmpty(chatRoom?.msgSearch)) return null;

    if (chatRoom?.msgSearch?.mode === MODE_UN_SEARCH) return null;

    if (chatRoom?.msgSearch?.mQuoteId) {
      return chatRoom?.searchMessages?.[chatRoom?.msgSearch?.mQuoteId];
    }

    return chatRoom?.searchMessages?.[chatRoom?.msgSearch?.id];
  }, [chatRoom?.msgSearch, chatRoom?.searchMessages]);

  const showSearchBlock = React.useMemo(() => {
    return chatRoom?.searching || !isEmpty(searchMessages);
  }, [searchMessages, chatRoom?.searching]);

  const messageFilter = chatRoom?.messageFilter;
  const disableReact = !getSetting('preaction');

  const isSearching = chatRoom?.searching;

  const itemAction = useItemActionDockChat();

  const items = React.useMemo(
    () => filterShowWhen(itemAction, { item: room }),
    [itemAction, room]
  );

  const handleScroll = evt => {
    if (chatRoom?.searching && !isEmpty(messageFilter)) return;

    if (isEmpty(searchMessages)) {
      if (evt.target.scrollTop < 100 && !loadingMsgs) {
        const { endLoadmoreMessage, lastMsgId } = chatRoom || {};

        if (!lastMsgId || endLoadmoreMessage) return;

        oldScrollOffset.current =
          evt.target.scrollHeight - evt.target.clientHeight;
        setLoadingMsgs(true);
        dispatch({
          type: 'chat/room/loadHistory',
          payload: { rid, lastMsgId },
          meta: {
            onSuccess: () => {
              setLoadingMsgs(false);
            }
          }
        });

        return;
      }
    }

    if (refSearchMessage?.current) {
      refSearchMessage.current.handleScroll(evt);
    }
  };

  const msgReactNode = React.useMemo(() => {
    return selectedMsg?.message ? formatGeneralMsg(selectedMsg?.message) : '';
  }, [selectedMsg]);

  const handleCustomAction = (types: string, payload?: any) => {
    if (!types) return;

    // convert types into Array
    const typeArray = types.split(/.,| /);

    typeArray.forEach(type => {
      switch (type) {
        case 'chat/replyMessage':
          setReactMode('reply');
          setSelectedMsg(payload);
          break;
        case 'chat/editMessage':
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

    if (showSearchBlock) {
      dispatch({
        type: 'chat/room/modeSearch',
        payload: { rid, mode: MODE_UN_SEARCH },
        meta: {
          onSuccess: () => {
            setTimeout(() => {
              if (refMessage?.current) {
                refMessage.current.scrollToBottom();
              }
            }, 300);
          }
        }
      });

      return;
    }

    if (refMessage?.current) {
      refMessage.current.scrollToBottom();
    }
  };

  return (
    <Panel>
      <PanelHeader searching={isSearching}>
        <PanelTitle loading={loading} room={{ ...data, t: 'd' }} user={user}>
          {data?.name}
        </PanelTitle>
        <PanelToolbar
          items={items}
          handleAction={handleAction}
          variant="roomPanel"
        />
      </PanelHeader>
      <SearchWrapper
        hide={!isSearching}
        roomId={rid}
        placeholder={i18n.formatMessage({ id: 'search_messages_dots' })}
        size={30}
        searching={isSearching}
      />
      <Box
        data-testid={camelCase('_chat Dock Content')}
        sx={{ flex: 1, minHeight: 0 }}
      >
        <ScrollContainer
          autoHide={false}
          autoHeight
          autoHeightMax={'100%'}
          ref={scrollRef}
          onScroll={handleScroll}
        >
          <PanelContent
            searching={isSearching}
            collapsed={collapsed}
            ref={scrollRef}
          >
            {(loading || chatRoom?.msgSearch?.loading) && (
              <LoadingStyled data-testid="loadingIndicator">
                <CircularProgress size={24} />
              </LoadingStyled>
            )}
            {isSearching ? (
              <MessageFilter
                items={messageFilter}
                room={data}
                disableReact
                handleAction={handleAction}
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
              />
            ) : (
              <>
                {isSearching ||
                loading ||
                !chatRoom?.endLoadmoreMessage ? null : (
                  <UIChatMsgStart
                    ata-testid={camelCase('_chat Start of conversation')}
                  >
                    {i18n.formatMessage({ id: 'start_of_conversation' })}
                  </UIChatMsgStart>
                )}
                <Messages
                  rid={rid}
                  groups={chatRoom?.groups}
                  groupIds={chatRoom?.groupIds}
                  preFetchingMsg={chatRoom?.preFetchingMsg}
                  newest={chatRoom?.newest}
                  room={data}
                  containerRef={scrollRef}
                  disableReact={disableReact}
                  handleAction={handleCustomAction}
                  ref={refMessage}
                  loading={loading}
                  roomProgress={chatRoom?.roomProgress}
                  showToolbar={!room?.is_block}
                  chatRoom={chatRoom}
                />
              </>
            )}
          </PanelContent>
        </ScrollContainer>
      </Box>

      {reactMode !== 'no_react' && (
        <ReplyEditWrapper>
          <ContentWrapper>
            <div>
              {reactMode === 'reply' ? (
                <TruncateText lines={1} variant="body2">
                  {i18n.formatMessage(
                    { id: 'chat_reply_to_user_at_timestamp' },
                    {
                      user_name: userSelectedMsg?.full_name,
                      is_owner: authUser?.id === userSelectedMsg?.id ? 1 : 0
                    }
                  )}
                </TruncateText>
              ) : (
                i18n.formatMessage({ id: 'editing' })
              )}
            </div>
            {!isEmpty(selectedMsg?.attachments) ? (
              <SelectedMsgAttachment>
                <LineIcon icon="ico-paperclip-alt" />
                <span>{i18n.formatMessage({ id: 'file_attachment' })} </span>
              </SelectedMsgAttachment>
            ) : (
              <SelectedMsg
                lines={1}
                dangerouslySetInnerHTML={{
                  __html: msgReactNode
                }}
              />
            )}
          </ContentWrapper>
          <LineIconClose icon="ico-close" onClick={handleCloseReactNode} />
        </ReplyEditWrapper>
      )}
      <FilesPreview
        ref={previewRef}
        filesUploadRef={filesUploadRef}
        rid={rid}
      />
      <PanelFooter
        searching={isSearching}
        isBlocked={room?.is_block}
        allowCompose={acl?.create}
      >
        <ChatSimpleComposer
          rid={rid}
          msgId={selectedMsg?.id}
          reactMode={reactMode}
          text={reactMode === 'edit' ? msgReactNode : textDefault || ''}
          onSuccess={handleComposeSuccess}
          previewRef={previewRef}
          ref={filesUploadRef}
          onMarkAsRead={handleMarkAsRead}
        />
      </PanelFooter>
    </Panel>
  );
}
