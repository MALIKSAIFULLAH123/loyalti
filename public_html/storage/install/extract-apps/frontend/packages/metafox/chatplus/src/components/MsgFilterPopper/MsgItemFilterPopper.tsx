import {
  useChatUserItem,
  useItemMenuMsgFilterPopper,
  useMsgItem,
  usePublicSettings,
  useRoomPermission,
  useSubscriptionItem
} from '@metafox/chatplus/hooks';
import { ChatMsgPassProps, RoomType, UserShape } from '@metafox/chatplus/types';
import {
  convertDateTime,
  countAttachmentImages
} from '@metafox/chatplus/utils';
import { useActionControl, useGlobal } from '@metafox/framework';
import { filterShowWhen } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import React from 'react';
import { TypeActionPinStar } from '../ChatRoomPanel/Header/Header';
import MsgReactions from '../MsgReactions';
import MsgToolbar from '../MsgToolbar';
import { JUMP_MSG_ACTION } from '@metafox/chatplus/constants';
import MsgAvatar from '../Messages/MsgAvatar';

const name = 'MsgItemFilterPopper';

const UIChatMsgItemBody = styled('div', {
  name,
  slot: 'uiChatMsgItemBody',
  shouldForwardProp: prop => prop !== 'file'
})<{ file?: any }>(({ theme, file }) => ({
  display: file ? 'block' : 'flex'
}));

const UIChatMsgSet = styled('div', {
  name,
  slot: 'uiChatMsgSet',
  shouldForwardProp: prop => prop !== 'isOwner' && prop !== 'isAlert'
})<{
  isOwner?: boolean;
  isAlert?: boolean;
}>(({ theme, isOwner, isAlert }) => ({
  display: 'flex',
  flexDirection: 'row',
  padding: theme.spacing(1.25),
  borderBottom: theme.mixins.border('secondary'),
  width: '100%',
  ...(isAlert && {
    '&.uiChatMsgSetAvatar': {
      display: 'none'
    },
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
  ...(isOwner && { flexDirection: 'row-reverse' })
}));

const UIChatMsgSetAvatar = styled('div', {
  name,
  slot: 'uiChatMsgSetAvatar'
})(({ theme }) => ({
  marginRight: theme.spacing(1)
}));

const ItemSuggestName = styled('span')(({ theme }) => ({
  ...theme.typography.h5
}));

const ItemDateStyled = styled('span')(({ theme }) => ({
  padding: theme.spacing(0.5, 0)
}));

const UIContentMsg = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  width: 'calc(100% - 40px)'
}));

interface MsgItemProps extends ChatMsgPassProps {
  msgId: string;
  showToolbar?: boolean;
  user: UserShape;
  type: TypeActionPinStar;
  closePopover?: any;
}

export default function MsgItemFilterPopper({
  msgId,
  user,
  tooltipPosition,
  isMobile,
  showToolbar = true,
  isRoomLimited,
  room,
  type,
  closePopover
}: MsgItemProps) {
  const identity = `chatplus.chatRooms.${room.id}.messageFilter.${msgId}`;
  const message = useMsgItem(identity);
  const { jsxBackend, dispatch } = useGlobal();
  const [handleAction, state] = useActionControl<{}, unknown>(identity, {});

  const onSuccess = () => {
    closePopover && closePopover();
  };

  const handleActionLocalFunc = (
    typeAction: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    if (typeAction === JUMP_MSG_ACTION && msgId) {
      dispatch({
        type: JUMP_MSG_ACTION,
        payload: { roomId: room?.id, mid: msgId, identity, type },
        meta: { onSuccess }
      });
    } else {
      handleAction(typeAction, payload, meta);
    }
  };

  const perms = useRoomPermission(room?.id);
  const settings = usePublicSettings();
  const subscription = useSubscriptionItem(room?.id);

  const allowMsgNoOne = !!(subscription?.allowMessageFrom === 'noone');

  const { _id, t, system, attachments, reactions, msgType, ts, u, starred } =
    message || {};

  const userInfo = useChatUserItem(u?._id);

  const isOwner = user._id === u?._id;
  const countImage = countAttachmentImages(attachments);
  const isImages = attachments?.length
    ? attachments.filter(item => item.image_url)?.length
    : null;

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

  if (!message) return null;

  return (
    <UIChatMsgSet>
      <UIChatMsgSetAvatar>
        <MsgAvatar
          name={userInfo?.name || u?.name}
          username={userInfo?.username || u?.username}
          avatarETag={u?.avatarETag || userInfo?.avatarETag}
          size={32}
        />
      </UIChatMsgSetAvatar>
      <UIContentMsg>
        <Box
          sx={{
            display: 'flex',
            flexDirection: 'row',
            justifyContent: 'space-between',
            alignItems: 'flex-start'
          }}
        >
          <Box sx={{ display: 'flex', flexDirection: 'column' }}>
            <ItemSuggestName>{u?.name || u?.username}</ItemSuggestName>
            <ItemDateStyled>{convertDateTime(ts?.$date)}</ItemDateStyled>
          </Box>
          <MsgToolbar
            identity={identity}
            items={itemActionMessages}
            handleAction={handleActionLocalFunc}
            disabled={false}
            disableReact
            state={state}
            showHover={false}
            menuStyles={{ maxWidth: 'auto' }}
            sxFieldWrapper={{ justifyContent: 'flex-start' }}
          />
        </Box>
        <div data-id={_id} data-t={t}>
          <UIChatMsgItemBody
            file={Boolean(
              countImage === 1 ||
                (!isImages && message?.file) ||
                message?.urls?.length
            )}
          >
            {jsxBackend.render({
              component: 'chatplus.messageContent.standard',
              props: {
                message,
                showActiveSearch: false,
                isOwner: false,
                user,
                createdDate: null,
                isRoomLimited,
                msgType,
                tooltipPosition: isMobile ? 'top' : tooltipPosition
              }
            })}
            <MsgToolbar
              identity={identity}
              handleAction={handleAction}
              disabled={system || !showToolbar}
              disableReact
              state={state}
            />
          </UIChatMsgItemBody>
          <MsgReactions
            identity={identity}
            disabled={!reactions || system}
            reactions={reactions}
          />
        </div>
      </UIContentMsg>
    </UIChatMsgSet>
  );
}
