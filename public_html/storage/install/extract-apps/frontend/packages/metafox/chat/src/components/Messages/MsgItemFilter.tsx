import { ChatMsgPassProps } from '@metafox/chat/types';
import { convertDateTime, countAttachmentImages } from '@metafox/chat/utils';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import MsgReactions from '../MsgReactions';

const name = 'MsgItemFilter';

const UIChatMsgItem = styled('div', {
  name,
  slot: 'uiChatMsgItem'
})(({ theme }) => ({
  maxWidth: '100%',
  overflow: 'hidden'
}));

const UIChatMsgItemBody = styled('div', {
  name,
  slot: 'uiChatMsgItemBody'
})(({ theme }) => ({
  display: 'flex'
}));

const ChatMsgItemBodyOuter = styled('div', {
  name,
  slot: 'ChatMsgItemBodyOuter'
})(({ theme }) => ({
  maxWidth: '100%'
}));

interface MsgItemProps extends ChatMsgPassProps {
  msgId: string;
  showToolbar?: boolean;
  tooltipPosition?: string;
  firstIndex?: boolean;
}

export default function MsgItemFilter({
  msgId,
  tooltipPosition = 'top',
  showToolbar = true,
  room,
  disableReact,
  handleAction,
  firstIndex = false
}: MsgItemProps) {
  const { jsxBackend, useSession, useGetItem, useActionControl } = useGlobal();
  const identity = `chat.chatRooms.${room?.id}.messageFilter.${msgId}`;
  const message = useGetItem(identity);

  const { user: authUser } = useSession();
  const [handleActionLocal] = useActionControl<{}, unknown>(identity, {});

  if (!message) return;

  const { id, attachments, reactions, type, created_at, user } = message || {};
  const isOwner = authUser?.id === user?.id;
  const createdDate = convertDateTime(created_at);
  const countImage = countAttachmentImages(attachments);

  const handleActionLocalFunc = (
    type: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    handleActionLocal(type, payload, meta);
    handleAction(type, message, meta);
  };

  return (
    <UIChatMsgItem className={'uiChatMsgItem'} data-id={id} data-t={type}>
      <UIChatMsgItemBody>
        <ChatMsgItemBodyOuter
          className={clsx('uiChatMsgItemBodyOuter', {
            hasToolbar: showToolbar,
            'multi-image': countImage > 1
          })}
        >
          {jsxBackend.render({
            component: `chat.messageContent.${type}`,
            props: {
              message,
              isOwner: false || isOwner,
              user,
              createdDate,
              msgType: type,
              tooltipPosition: firstIndex ? 'bottom' : tooltipPosition,
              isSearch: true,
              showActiveSearch: false
            }
          })}
        </ChatMsgItemBodyOuter>
      </UIChatMsgItemBody>
      <MsgReactions
        identity={identity}
        disabled={disableReact}
        reactions={reactions}
        handleAction={handleActionLocalFunc}
      />
    </UIChatMsgItem>
  );
}
