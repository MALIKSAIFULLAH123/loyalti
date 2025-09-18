import {
  useItemMenuMsgFilterPopper,
  useMsgItem
} from '@metafox/chatplus/hooks';
import { ChatMsgPassProps, RoomType, UserShape } from '@metafox/chatplus/types';
import { convertDateTime } from '@metafox/chatplus/utils';
import { useActionControl, useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import MsgReactions from '../MsgReactions';
import MsgToolbar from '../MsgToolbar';
import { filterShowWhen } from '@metafox/utils';
import { JUMP_MSG_ACTION } from '@metafox/chatplus/constants';

const name = 'MsgItemFilter';

const UIChatMsgItem = styled('div', {
  name,
  slot: 'uiChatMsgItem'
})(({ theme }) => ({
  maxWidth: '100%',
  width: '100%'
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
  user: UserShape;
  type?: any;
  firstIndex?: boolean;
  endIndex?: boolean;
}

export default function MsgItemFilter({
  msgId,
  user,
  tooltipPosition = 'bottom',
  isMobile,
  showToolbar = true,
  isRoomLimited,
  room,
  disableReact,
  subscription,
  settings,
  perms,
  type,
  firstIndex = false,
  endIndex = false
}: MsgItemProps) {
  const identity = `chatplus.chatRooms.${room.id}.messageFilter.${msgId}`;
  const message = useMsgItem(identity);
  const { jsxBackend, dispatch } = useGlobal();
  const [handleAction] = useActionControl<{}, unknown>(identity, {});

  const { _id, t, system, reactions, msgType, ts, u, starred } = message || {};
  const isOwner = user._id === u?._id;
  const createdDate = convertDateTime(ts?.$date);

  const handleActionLocal = (
    typeAction: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    if (typeAction === JUMP_MSG_ACTION && msgId) {
      dispatch({
        type: JUMP_MSG_ACTION,
        payload: { roomId: room?.id, mid: msgId, identity }
      });
    } else {
      handleAction(typeAction, payload, meta);
    }
  };

  const isMetaFoxBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.metafoxBlocked || subscription?.metafoxBlocker)
  );

  const isNormalBlocked = !!(
    room?.t === RoomType.Direct &&
    (subscription?.blocked || subscription?.blocker)
  );
  const isBlocked = !!(isNormalBlocked || isMetaFoxBlocked);

  const isStarred = starred?.find(x => x._id === user._id);

  const allowMsgNoOne = !!(subscription?.allowMessageFrom === 'noone');

  const allowStarring =
    settings?.Message_AllowStarring && !allowMsgNoOne && !isBlocked;
  const allowPinning =
    settings.Message_AllowPinning &&
    perms['pin-message'] &&
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

  const itemAction = useItemMenuMsgFilterPopper();

  const itemActionMessages = React.useMemo(
    () =>
      filterShowWhen(itemAction, {
        allowPinning,
        item: message,
        allowStarring,
        isStarred,
        canDelete,
        type
      }),
    [
      itemAction,
      allowPinning,
      message,
      allowStarring,
      isStarred,
      canDelete,
      type
    ]
  );

  return (
    <UIChatMsgItem data-id={_id} data-t={t}>
      <UIChatMsgItemBody>
        <ChatMsgItemBodyOuter>
          {jsxBackend.render({
            component: 'chatplus.messageContent.standard',
            props: {
              message,
              isOwner,
              user,
              createdDate,
              isRoomLimited,
              msgType,
              tooltipPosition: firstIndex
                ? 'bottom'
                : endIndex || isMobile
                ? 'top'
                : tooltipPosition,
              isMessageFilter: true
            }
          })}
        </ChatMsgItemBodyOuter>
        <MsgToolbar
          identity={identity}
          items={itemActionMessages}
          handleAction={handleActionLocal}
          disabled={false}
          disableReact
        />
      </UIChatMsgItemBody>
      <MsgReactions
        identity={identity}
        disabled={!reactions || system}
        reactions={reactions}
      />
    </UIChatMsgItem>
  );
}
