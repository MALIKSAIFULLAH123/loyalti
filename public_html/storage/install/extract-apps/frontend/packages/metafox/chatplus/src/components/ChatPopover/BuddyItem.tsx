import { menuStyles } from '@metafox/chatplus/constants';
import {
  useChatRoom,
  useChatUserItem,
  useIsSelfChat,
  useItemActionRoomItem,
  usePublicSettings,
  useRoomItem,
  useRoomPermission,
  useSessionUser,
  useSubscriptionItem
} from '@metafox/chatplus/hooks';
import { getBuddyItem } from '@metafox/chatplus/selectors';
import { RoomItemShape, RoomType } from '@metafox/chatplus/types';
import { GlobalState, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, IconButton, styled, Tooltip } from '@mui/material';
import { isArray, isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import { useLocation, useNavigate } from 'react-router-dom';
import Avatar from '../Avatar';
import FromNowChat from '../FromNowChat';
import LastMessage from '../Messages/LastMessage';

const RootBuddyItem = styled('div', {
  shouldForwardProp: props => props !== 'unread' && props !== 'isActive'
})<{ unread: boolean; isActive?: boolean }>(({ theme, unread, isActive }) => ({
  position: 'relative',
  margin: theme.spacing(0, 1),
  padding: theme.spacing(0.5, 2),
  cursor: 'pointer',
  transition: 'background-color 300ms ease',
  '&:hover': {
    background: theme.palette.action.hover,
    borderRadius: theme.shape.borderRadius
  },
  '&:hover .uiChatItemBtn': {
    visibility: 'visible'
  },
  ...(isActive && {
    '.uiChatItemBtn': {
      visibility: 'visible'
    }
  })
}));
const ItemWrapper = styled('div')(({ theme }) => ({
  flex: 1,
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(1.5, 0),
  color: theme.palette.grey['700'],
  fontSize: theme.spacing(1.75),
  cursor: 'pointer',
  overflow: 'hidden'
}));
const ItemMedia = styled('div')(({ theme }) => ({
  marginRight: theme.spacing(1)
}));
const ItemInner = styled('div')(({ theme }) => ({
  flex: 1,
  minWidth: '0'
}));
const ItemRowTitleWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between'
}));
const ItemTitle = styled('div')(({ theme }) => ({
  fontWeight: 'bold',
  fontSize: theme.spacing(1.875),
  lineHeight: theme.spacing(2.25),
  color: theme.palette.text.primary,
  flex: 1,
  minWidth: 0,
  maxWidth: '100%',
  whiteSpace: 'nowrap',
  textOverflow: 'ellipsis',
  overflow: 'hidden'
}));
const ItemSubtitle = styled('div')(({ theme }) => ({
  color: theme.palette.text.secondary,
  display: 'inline-flex',
  alignItems: 'center',
  fontSize: theme.spacing(1.625),
  lineHeight: theme.spacing(2.35)
}));

const RootItemAction = styled('div', {
  slot: 'root',
  shouldForwardProp: props => props !== 'isSelfChat'
})<{ isSelfChat?: boolean }>(({ theme, isSelfChat }) => ({
  ...(isSelfChat && {
    minWidth: '24px',
    minHeight: '40px'
  }),
  right: theme.spacing(1),
  top: '50%',
  transform: 'translateY(-50%)',
  position: 'absolute',
  visibility: 'hidden'
}));

const UIChatItemBtn = styled(IconButton, {
  slot: 'UIChatItemBtn',
  shouldForwardProp: props => props !== 'isActive'
})<{ isActive?: boolean }>(({ theme, isActive }) => ({
  position: 'relative',
  padding: theme.spacing(1, 0.5),
  cursor: 'pointer',
  minWidth: theme.spacing(3),
  lineHeight: theme.spacing(2.5),
  width: '35px',
  height: '35px',
  borderRadius: 40,
  background:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['600']
      : theme.palette.grey['50'],
  '&:hover': {
    background:
      theme.palette.mode === 'dark'
        ? theme.palette.grey['600']
        : theme.palette.grey['50']
  },
  ...(isActive && {
    boxShadow: theme.shadows[2]
  })
}));

const StyledTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    ...(theme.palette.mode === 'dark' && {
      border: theme.mixins.border('secondary')
    })
  },
  '& .MuiTooltip-arrow': {
    '&::before': {
      ...(theme.palette.mode === 'dark' && {
        border: theme.mixins.border('secondary')
      })
    }
  }
}));

interface State {}
interface Props {
  item: RoomItemShape & {
    no_join?: boolean;
    spotlight_user?: boolean;
    username?: string;
  };
  closePopover?: any;
  handleResetSearch?: any;
  setActive?: any;
  active?: any;
}

export default function BuddyItem({
  item,
  closePopover,
  handleResetSearch,
  setActive,
  active
}: Props) {
  const { usePageParams, dispatch, i18n, ItemActionMenu, useActionControl } =
    useGlobal();
  const pageParams = usePageParams();
  const location = useLocation() as any;
  const navigate = useNavigate();

  const { rid } = pageParams;

  const isSelfChat = useIsSelfChat(item.id);
  const itemAction = useItemActionRoomItem();

  const buddy = useSelector((state: GlobalState) =>
    getBuddyItem(state, item.id)
  );

  const subscription = useSubscriptionItem(item.id);
  const userChat = useChatUserItem(item?.userId);
  const chatRoom = useChatRoom(item?.id);
  const room = useRoomItem(item?.id);
  const settings = usePublicSettings();
  const perms = useRoomPermission(item?.id);
  const userSession = useSessionUser();

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

  const isMuted =
    userSession &&
    room &&
    room.muted &&
    isArray(room.muted) &&
    !!room.muted.find(x => x === userSession.username);

  const [handleAction] = useActionControl<State, unknown>(item?.id, {});

  const items = React.useMemo(
    () =>
      filterShowWhen(itemAction, {
        room: item,
        subscription,
        groups: chatRoom?.groups,
        favorite: subscription?.f,
        settings,
        perms,
        isBlocked,
        isMetaFoxBlocked,
        isMuted,
        allowMsgNoOne,
        isSelfChat
      }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [
      chatRoom,
      subscription,
      room,
      settings,
      perms,
      itemAction,
      isBlocked,
      isMetaFoxBlocked,
      isMuted,
      allowMsgNoOne,
      isSelfChat
    ]
  );

  const openChatRoom = React.useCallback(
    evt => {
      closePopover && closePopover();

      if (item?.no_join || !subscription) {
        if (item?.spotlight_user) {
          dispatch({
            type: 'chatplus/chatRoom/newConversation',
            payload: { users: [{ value: item?.username, label: item?.name }] }
          });
        } else {
          dispatch({
            type: 'chatplus/room/openChatRoomFromBuddy',
            payload: { rid: item.id || item._id }
          });
        }

        handleResetSearch && handleResetSearch();

        return;
      }

      if (location.pathname === '/messages' || rid) {
        navigate({
          pathname: `/messages/${item.id}`
        });
      } else {
        dispatch({
          type: 'chatplus/room/openBuddy',
          payload: { rid: item.id }
        });
      }

      handleResetSearch && handleResetSearch();
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item, closePopover]
  );

  const status = isSelfChat ? 1 : userChat?.status || 0;

  const unread =
    !item?.no_join &&
    !isEmpty(item?.lastMessage) &&
    (subscription?.alert || !!subscription?.unread);

  const handleClickActive = () => {
    if (active === item.id) {
      setActive(null);

      return;
    }

    setActive(item.id);
  };

  return (
    <RootBuddyItem unread={unread} isActive={active === item.id}>
      <ItemWrapper onClick={openChatRoom}>
        <ItemMedia>
          <Avatar
            name={buddy?.name || item.name}
            username={buddy?.username || item.fname || item.username}
            avatarETag={buddy?.avatarETag || item?.avatarETag}
            size={40}
            src={buddy?.avatar}
            status={status}
            room={item}
          />
        </ItemMedia>
        <ItemInner>
          <ItemRowTitleWrapper>
            <ItemTitle>{buddy?.name || item.fname || item.name}</ItemTitle>
            {!item?.no_join ? (
              <ItemSubtitle>
                <FromNowChat
                  value={item?.lastMessage?._updatedAt?.$date}
                  format="ll"
                />
              </ItemSubtitle>
            ) : null}
          </ItemRowTitleWrapper>
          <LastMessage
            room={item}
            unread={unread}
            subscription={subscription}
          />
        </ItemInner>
      </ItemWrapper>
      {!item?.no_join && subscription ? (
        <RootItemAction
          isSelfChat={isSelfChat}
          className="uiChatItemBtn"
          onClick={handleClickActive}
        >
          <ItemActionMenu
            items={items}
            placement="bottom-end"
            handleAction={handleAction}
            scrollClose
            popperOptions={{
              strategy: 'fixed'
            }}
            menuStyles={menuStyles}
            variantPopper="hidden-outview"
            control={
              <Box>
                <StyledTooltip
                  title={i18n.formatMessage({ id: 'more' })}
                  placement="top"
                >
                  <UIChatItemBtn
                    disableFocusRipple
                    disableRipple
                    disableTouchRipple
                    isActive={active === item.id}
                  >
                    <LineIcon icon="ico-dottedmore-vertical-o" />
                  </UIChatItemBtn>
                </StyledTooltip>
              </Box>
            }
          />
        </RootItemAction>
      ) : null}
    </RootBuddyItem>
  );
}
