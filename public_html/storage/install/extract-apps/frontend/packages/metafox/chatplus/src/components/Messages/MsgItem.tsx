import useMessagesContext, {
  useChatRoom,
  useItemActionMessage,
  useMsgItem,
  usePublicSettings,
  useReactionChat,
  useRoomPermission
} from '@metafox/chatplus/hooks';
import {
  ChatMsgPassProps,
  IPropClickAction,
  RoomType,
  UserShape
} from '@metafox/chatplus/types';
import {
  convertDateTime,
  countAttachmentImages,
  filterImageAttachment,
  getReactionExist,
  isNotSeenMsg
} from '@metafox/chatplus/utils';
import { useActionControl, useGlobal, useScrollRef } from '@metafox/framework';
import { filterShowWhen } from '@metafox/utils';
import { styled } from '@mui/material';
import React from 'react';
import MsgReactions from '../MsgReactions';
import MsgToolbar from '../MsgToolbar';
import MsgSeenUsers from './MsgSeenUsers';
import { formatTextCopy } from '@metafox/chatplus/services/formatTextMsg';
import { LineIcon } from '@metafox/ui';
import { checkIsQuote } from '../MsgContent/MessageText';
import ChatBotAction from './ChatBotAction';
interface MsgItemProps extends ChatMsgPassProps {
  msgId: string;
  isSearch?: boolean;
  showToolbar?: boolean;
  user: UserShape;
  toggleSearch?: () => void;
  indexLast?: any;
}
const name = 'MsgItem';

const UIChatMsgItem = styled('div', {
  name,
  slot: 'uiChatMsgItem',
  shouldForwardProp: prop => prop !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  ...(isOwner && {
    flexDirection: 'row-reverse'
  }),
  minHeight: '40px',
  marginBottom: theme.spacing(0.5)
}));

const UIChatMsgItemBody = styled('div', {
  name,
  slot: 'uiChatMsgItemBody',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'isAlert' && prop !== 'isShowReact'
})<{ isOwner?: boolean; isAlert?: boolean; isShowReact?: boolean }>(
  ({ theme, isOwner, isAlert, isShowReact }) => ({
    display: 'flex',
    '@media (hover: hover)': {
      '&:hover .uiChatItemBtn': {
        visibility: 'visible'
      }
    },
    '@media (hover: none)': {
      '.uiChatItemBtn': {
        visibility: 'visible'
      }
    },
    [theme.breakpoints.down('sm')]: {
      '.uiChatItemBtn': {
        visibility: 'visible'
      }
    },
    ...(isOwner && {
      flexDirection: 'row-reverse'
    }),
    ...(isAlert && {
      textAlign: 'center',

      ...(isShowReact && {
        justifyContent: 'center'
      })
    })
  })
);
const UIChatMsgItemBodyOuter = styled('div', {
  name,
  slot: 'uiChatMsgItemBodyOuter',
  shouldForwardProp: prop =>
    prop !== 'isPageAllMessages' &&
    prop !== 'msgType' &&
    prop !== 'isMsgEdit' &&
    prop !== 'totalImage' &&
    prop !== 'totalImageQuote' &&
    prop !== 'msgContentType'
})<{
  isPageAllMessages?: boolean;
  msgType?: string;
  isMsgEdit?: boolean;
  totalImage?: number;
  totalImageQuote?: number;
  msgContentType?: string;
}>(
  ({
    theme,
    isPageAllMessages,
    msgType,
    isMsgEdit,
    totalImage,
    totalImageQuote,
    msgContentType
  }) => ({
    minWidth: 0,

    maxWidth: `calc(100% - ${isMsgEdit ? '80px' : '50px'})`,
    ...(totalImage > 1 && { width: '75%' }),
    ...(totalImage === 1 && { width: '65%' }),
    ...((msgType === 'message_pinned' || msgType === 'message_unpinned') && {
      width: '100%'
    }),
    ...(msgContentType === 'messageDeleted' && {
      maxWidth: 'calc(100% - 26px)'
    }),

    // isPageAllMessages
    ...(isPageAllMessages && {
      maxWidth: '65%',
      ...((msgType === 'message_pinned' || msgType === 'message_unpinned') && {
        maxWidth: totalImageQuote === 1 ? '300px' : '400px',
        width: '100%'
      }),
      ...(totalImage === 1 && {
        maxWidth: '300px',
        width: '100%'
      }),
      ...(totalImage === 2 && { width: '36.67%' }),
      ...(totalImage > 2 && { width: '55%' }),

      // mobile
      [theme.breakpoints.down('sm')]: {
        maxWidth: `calc(100% - ${isMsgEdit ? '80px' : '50px'})`,
        ...(totalImage > 1 && { width: '75%' }),
        ...(totalImage === 1 && { width: '60%' }),
        ...((msgType === 'message_pinned' ||
          msgType === 'message_unpinned') && {
          width: '75%'
        })
      }
    })
  })
);

export default function MsgItem({
  msgId,
  user,
  tooltipPosition,
  isMobile,
  showToolbar = true,
  disableReact,
  seenUserShow,
  isRoomLimited,
  handleAction,
  isReadonly,
  isRoomOwner,
  room,
  subscription,
  indexLast
}: MsgItemProps) {
  const identity = `chatplus.entities.message.${msgId}`;
  const message = useMsgItem(identity);
  const { jsxBackend, usePageParams } = useGlobal();
  const [isShowHover, setIsShowHover] = React.useState(true);
  const showActionRef = React.useRef(null);
  const scrollRef = useScrollRef();
  const isChatBot = room?.isBotRoom;

  const settings = usePublicSettings();
  const perms = useRoomPermission(room?.id);

  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  const [handleActionLocal] = useActionControl<{}, unknown>(identity, {});

  const itemAction = useItemActionMessage();

  const {
    _id,
    t,
    system,
    attachments,
    reactions: reactionMsg,
    msgType,
    starred = [],
    ts,
    u,
    msgContentType,
    editedBy
  } = message || {};

  const { dataQuote } = checkIsQuote(attachments);

  const { count: totalImageQuote } = filterImageAttachment(
    dataQuote?.attachments
  );

  const reactionList = useReactionChat();
  const reactions = getReactionExist(reactionMsg, reactionList);

  const isOwner = user._id === u?._id;
  const createdDate = convertDateTime(ts?.$date);
  const countImage = countAttachmentImages(attachments);
  const isAlert = isNotSeenMsg(t);

  const isMetaFoxBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.metafoxBlocked || subscription?.metafoxBlocker)
  );

  const isNormalBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.blocked || subscription?.blocker)
  );
  const isBlocked = !!(isNormalBlocked || isMetaFoxBlocked);

  const allowQuote = false;
  const isSearch = false;
  const isStarred = starred.find(x => x._id === user._id);
  const allowMsgNoOne = !!(subscription?.allowMessageFrom === 'noone');
  const allowReply =
    (isRoomOwner || !isReadonly) &&
    !isRoomLimited &&
    !allowMsgNoOne &&
    !isBlocked;
  const allowStarring =
    settings.Message_AllowStarring && !allowMsgNoOne && !isBlocked;
  const allowPinning =
    settings.Message_AllowPinning &&
    perms['pin-message'] &&
    !isRoomLimited &&
    !allowMsgNoOne &&
    !isBlocked;
  const allowEdit =
    settings['Message_AllowEditing'] &&
    (perms['edit-message'] || isOwner) &&
    !isRoomLimited &&
    !allowMsgNoOne &&
    !isBlocked;
  const canDelete =
    settings.Message_AllowDeleting &&
    (perms['force-delete-message'] ||
      perms['delete-message'] ||
      (isOwner && perms['delete-own-message'])) &&
    !isRoomLimited &&
    !allowMsgNoOne &&
    !isBlocked;

  const allowCopy = formatTextCopy(message);

  const itemActionMessages = React.useMemo(
    () =>
      filterShowWhen(itemAction, {
        allowReply,
        allowQuote,
        isSearch,
        allowEdit,
        allowPinning,
        item: message,
        allowStarring,
        isStarred,
        canDelete,
        room,
        allowCopy
      }),
    [
      itemAction,
      allowReply,
      allowQuote,
      isSearch,
      allowEdit,
      allowPinning,
      message,
      allowStarring,
      isStarred,
      canDelete,
      room,
      allowCopy
    ]
  );

  const handleActionLocalFunc = (
    type: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    handleActionLocal(type, payload, meta);
    handleAction(type, message, meta);
  };

  const isShowReact =
    msgContentType !== 'messageDeleted' &&
    msgType !== 'message_pinned' &&
    msgType !== 'message_unpinned';

  const isMsgEdit = editedBy?.username && msgContentType !== 'messageDeleted';

  const context = useMessagesContext();
  const chatRoom = useChatRoom(room?.id);
  const { msgSearch } = chatRoom || {};

  const { scrollToBottomNewest } = context;

  const refMsg = React.useRef<any>();

  React.useEffect(() => {
    if (refMsg?.current && indexLast) {
      scrollToBottomNewest(refMsg.current.clientHeight);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [indexLast]);

  const onClickAction = React.useCallback(
    ({ identity: id, type }: IPropClickAction) => {
      if (
        showActionRef?.current?.id === id &&
        !showActionRef?.current?.showHover &&
        showActionRef?.current?.type === type
      ) {
        setIsShowHover(true);
        showActionRef.current = null;

        return;
      }

      setIsShowHover(identity === id ? false : true);
      showActionRef.current = {
        id,
        showHover: identity === id ? false : true,
        type
      };
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [identity, isShowHover, showActionRef?.current]
  );

  React.useEffect(() => {
    if (msgSearch?.loading || !refMsg.current) return;

    if (
      msgSearch?.mQuoteId === message?._id ||
      msgSearch?.id === message?._id
    ) {
      const { top } = refMsg.current?.getBoundingClientRect();
      const { top: topScrollRef } = scrollRef.current?.getBoundingClientRect();

      // msg visible in viewport
      if (
        top - topScrollRef > 0 &&
        top - topScrollRef < scrollRef.current?.clientHeight
      )
        return;

      scrollRef.current.scrollTo({ top: refMsg.current?.offsetTop });
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [msgSearch?.loading, msgSearch?.id, msgSearch?.mQuoteId, message?._id]);

  if (!message) {
    return null;
  }

  return (
    <UIChatMsgItem
      id={identity}
      isOwner={isOwner}
      data-id={_id}
      data-t={t}
      data-testid={_id}
      ref={refMsg}
    >
      <UIChatMsgItemBody
        isOwner={isOwner}
        isAlert={isAlert}
        isShowReact={isShowReact}
      >
        <UIChatMsgItemBodyOuter
          isMsgEdit={isMsgEdit}
          msgContentType={msgContentType}
          totalImage={countImage}
          isPageAllMessages={isPageAllMessages}
          msgType={msgType}
          totalImageQuote={totalImageQuote}
        >
          {jsxBackend.render({
            component: `chatplus.messageContent.${msgContentType}`,
            props: {
              message,
              isOwner,
              user,
              createdDate,
              isRoomLimited,
              msgType,
              tooltipPosition: indexLast || isMobile ? 'top' : tooltipPosition,
              scrollMessage: () => {}
            }
          })}
        </UIChatMsgItemBodyOuter>
        {isMsgEdit ? (
          <LineIcon
            icon="ico-pencil"
            sx={{ alignSelf: 'center', mr: 1, ml: 1 }}
          />
        ) : null}
        {isShowReact && !isAlert && (
          <MsgToolbar
            identity={identity}
            handleAction={handleActionLocalFunc}
            disabled={system || !showToolbar}
            disableReact={disableReact}
            items={itemActionMessages}
            isOwner={isOwner}
            showHover={isShowHover}
            onClickAction={onClickAction}
            showActionRef={showActionRef}
            placement={indexLast ? 'left-end' : undefined}
          />
        )}
      </UIChatMsgItemBody>
      {isShowReact && (
        <MsgReactions
          identity={identity}
          disabled={!reactions || system}
          reactions={reactions}
          isOwner={isOwner}
        />
      )}
      {isChatBot && !isOwner ? (
        <ChatBotAction message={message} room={room} />
      ) : null}
      <MsgSeenUsers
        disabled={!seenUserShow}
        message={message}
        user={user}
        isOwner={isOwner}
        indexLast={indexLast}
      />
    </UIChatMsgItem>
  );
}
