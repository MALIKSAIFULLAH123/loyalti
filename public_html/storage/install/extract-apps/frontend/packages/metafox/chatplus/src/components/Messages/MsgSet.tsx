import { useChatUserItem } from '@metafox/chatplus/hooks';
import { ChatMsgPassProps, MsgSetShape } from '@metafox/chatplus/types';
import { isAlertMsg } from '@metafox/chatplus/utils';
import { styled } from '@mui/material';
import React from 'react';
import MsgAvatar from './MsgAvatar';
import MsgItem from './MsgItem';

interface MessageSetProps extends ChatMsgPassProps {
  msgSet: MsgSetShape;
  showToolbar?: boolean;
}

const name = 'MsgSet';

const UIChatMsgSet = styled('div', {
  name,
  slot: 'uiChatMsgSet',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'isAlert' && prop !== 'isGroup'
})<{ isOwner?: boolean; isAlert?: boolean; isGroup?: boolean }>(
  ({ theme, isOwner, isAlert, isGroup }) => ({
    display: 'flex',
    flexDirection: 'row',
    padding: theme.spacing(0.5, 2),
    ...(isAlert && {
      '&.uiChatMsgItemCall': {
        border: 'none'
      },
      '&.uiChatMsgActions': {
        justifyContent: 'center',
        '&.my-1; .btn': {
          '&.mx-1': '!important'
        }
      }
    }),
    ...(isOwner && {
      flexDirection: 'row-reverse',
      padding: theme.spacing(0, 2)
    })
  })
);

const UIChatMsgSetAvatar = styled('div', {
  name,
  slot: 'uiChatMsgSetAvatar',
  shouldForwardProp: prop => prop !== 'isOwner' && prop !== 'isAlert'
})<{ isOwner?: boolean; isAlert?: boolean }>(({ theme, isOwner, isAlert }) => ({
  marginRight: theme.spacing(1),
  display: 'flex',
  alignItems: 'flex-end',
  marginBottom: theme.spacing(0.75),
  ...(isOwner && { display: 'none', flexDirection: 'row-reverse' }),
  ...(isAlert && { display: 'none' })
}));

const UIChatMsgSetBody = styled('div', {
  name,
  slot: 'uiChatMsgSetBody',
  shouldForwardProp: prop => prop !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  flex: 1,
  minWidth: 0,
  ...(isOwner && { flexDirection: 'row-reverse' })
}));

const UIChatMsgSetBodyUser = styled('div', {
  name,
  slot: 'uiChatMsgSetBodyUser'
})(({ theme }) => ({
  ...theme.typography.body2,
  color: theme.palette.text.secondary,
  marginBottom: theme.spacing(0.5),
  padding: theme.spacing(0, 0.5)
}));

export default function MsgSet({
  msgSet,
  archived,
  settings,
  perms,
  tooltipPosition = 'top',
  isMobile,
  disableReact,
  room,
  subscription,
  user,
  seenUserShow,
  isReadonly,
  isRoomOwner,
  isRoomLimited,
  handleAction,
  showToolbar = true
}: MessageSetProps) {
  const userInfo = useChatUserItem(msgSet?.u?._id);

  if (!msgSet) return;

  const { u, items, t } = msgSet;

  const isOwner = user._id === u._id;
  const isAlert = isAlertMsg(t);
  const isGroup = room && ['p', 'c'].includes(room.t);

  if (!items || !items.length) {
    return null;
  }

  return (
    <UIChatMsgSet
      isOwner={isOwner}
      isAlert={isAlert}
      isGroup={isGroup}
      className={`uiChatMsgSet ${isOwner ? 'isOwner' : ''} ${
        isAlert ? 'roomAlert' : ''
      } ${isGroup ? 'roomGroup' : ''}`}
    >
      <UIChatMsgSetAvatar
        isOwner={isOwner}
        isAlert={isAlert}
        className={'uiChatMsgSetAvatar'}
      >
        <MsgAvatar
          name={userInfo?.name || u?.name}
          username={userInfo?.username || u?.username}
          avatarETag={userInfo?.avatarETag || u?.avatarETag}
          size={32}
          showTooltip
        />
      </UIChatMsgSetAvatar>
      <UIChatMsgSetBody className={'uiChatMsgSetBody'}>
        {isGroup && !isOwner && !isAlert && (
          <UIChatMsgSetBodyUser>{u.name}</UIChatMsgSetBodyUser>
        )}
        {items.map((msgId, i) => (
          <MsgItem
            indexLast={i === items.length - 1}
            user={user}
            settings={settings}
            archived={archived}
            key={msgId.toString()}
            msgId={msgId}
            room={room}
            subscription={subscription}
            tooltipPosition={tooltipPosition}
            isMobile={isMobile}
            disableReact={disableReact}
            seenUserShow={seenUserShow && i + 1 === items.length}
            isReadonly={isReadonly}
            isRoomLimited={isRoomLimited}
            isRoomOwner={isRoomOwner}
            handleAction={handleAction}
            showToolbar={showToolbar}
          />
        ))}
      </UIChatMsgSetBody>
    </UIChatMsgSet>
  );
}
