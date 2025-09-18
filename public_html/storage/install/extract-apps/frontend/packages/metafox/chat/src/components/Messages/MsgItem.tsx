import { useChatRoom, useReactionChat } from '@metafox/chat/hooks';
import { ChatMsgPassProps, RoomItemShape } from '@metafox/chat/types';
import {
  convertDateTime,
  getReactionExist,
  isNotSeenMsg
} from '@metafox/chat/utils';
import MsgReactions from '../MsgReactions';
import { useActionControl, useGlobal, useScrollRef } from '@metafox/framework';
import { UserItemShape } from '@metafox/user';
import { styled } from '@mui/material';
import React from 'react';
import MsgToolbar from '../MsgToolbar';
import { UIMsgItemBody, UIMsgItemBodyOuter } from '../Wrapper/MsgItem';
import { useCheckImageFile } from '../MsgAttachments/MsgAttachments';

interface MsgItemProps extends ChatMsgPassProps {
  msgId: string;
  isSearch?: boolean;
  showToolbar?: boolean;
  authUser: UserItemShape;
  toggleSearch?: () => void;
  room?: RoomItemShape;
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

export default function MsgItem({
  msgId,
  authUser,
  showToolbar = true,
  disableReact,
  handleAction,
  room,
  indexLast
}: MsgItemProps) {
  const { jsxBackend, usePageParams, useGetItem } = useGlobal();
  const scrollRef = useScrollRef();
  const refMsg = React.useRef<any>();

  const identity = `chat.entities.message.${msgId}`;
  const message = useGetItem(identity);

  const chatRoom = useChatRoom(room?.id);
  const { msgSearch, msgNewest } = chatRoom || {};

  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  const [handleActionLocal] = useActionControl<{}, unknown>(identity, {});

  const {
    id,
    type,
    attachments,
    reactions: reactionMsg,
    created_at,
    user
  } = message || {};

  const reactionList = useReactionChat();
  const reactions = getReactionExist(reactionMsg, reactionList);

  const userMsg = useGetItem(user);

  const isOwner = authUser?.id === userMsg?.id;
  const createdDate = convertDateTime(created_at);
  const isAlert = isNotSeenMsg(type);

  const { multiImageFile: countImage } = useCheckImageFile(attachments);

  React.useEffect(() => {
    if (msgSearch?.loading || !refMsg.current) return;

    if (msgSearch?.mQuoteId === message?.id || msgSearch?.id === message?.id) {
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

  const placement = React.useMemo(() => {
    // eslint-disable-next-line eqeqeq
    if (isOwner)
      return indexLast && msgNewest == id ? 'left-end' : 'left-start';

    return indexLast && msgNewest == id ? 'right-end' : 'right-start';
  }, [isOwner, indexLast, msgNewest, id]);

  if (!message) return null;

  const handleActionLocalFunc = (
    type: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    handleActionLocal(type, payload, meta);
    handleAction(type, message, meta);
  };

  const isShowReact = type !== 'messageDeleted';

  return (
    <UIChatMsgItem
      isOwner={isOwner}
      data-id={id}
      data-t={type}
      data-testid={id}
      ref={refMsg}
    >
      <UIMsgItemBody
        isOwner={isOwner}
        isAlert={isAlert}
        isShowReact={isShowReact}
      >
        <UIMsgItemBodyOuter
          totalImage={countImage?.length}
          isPageAllMessages={isPageAllMessages}
          msgType={type}
        >
          {jsxBackend.render({
            component: `chat.messageContent.${type}`,
            props: {
              message,
              isOwner,
              user,
              createdDate,
              msgType: type,
              tooltipPosition: 'top'
            }
          })}
        </UIMsgItemBodyOuter>
        {isShowReact && !isAlert && (
          <MsgToolbar
            identity={identity}
            handleAction={handleActionLocalFunc}
            disabled={!showToolbar}
            disableReact={disableReact}
            isOwner={isOwner}
            placement={placement}
          />
        )}
      </UIMsgItemBody>
      {isShowReact && (
        <MsgReactions
          identity={identity}
          disabled={!reactions}
          reactions={reactions}
          isOwner={isOwner}
          handleAction={handleActionLocalFunc}
        />
      )}
    </UIChatMsgItem>
  );
}
