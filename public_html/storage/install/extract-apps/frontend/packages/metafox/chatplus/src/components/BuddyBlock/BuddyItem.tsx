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
import { ClickOutsideListener, LineIcon } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, IconButton, styled, Tooltip } from '@mui/material';
import { camelCase, isArray, isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import { useLocation } from 'react-router-dom';
import Avatar from '../Avatar';
import FromNowChat from '../FromNowChat';
import LastMessage from '../Messages/LastMessage';

const RootBuddyItem = styled('div', {
  name: 'Chat',
  slot: 'rootBuddyItem',
  overridesResolver(props, styles) {
    return [styles.rootBuddyItem];
  },
  shouldForwardProp: props =>
    props !== 'unread' &&
    props !== 'isFocus' &&
    props !== 'isMobile' &&
    props !== 'isActive'
})<{
  unread: boolean;
  isFocus?: boolean;
  isMobile?: boolean;
  isActive?: boolean;
}>(({ theme, unread, isFocus, isMobile, isActive }) => ({
  marginRight: theme.spacing(1),
  marginLeft: theme.spacing(0.5),
  padding: theme.spacing(0.5, 1.5, 0.5, 2),
  cursor: 'pointer',
  transition: 'background-color 300ms ease',
  display: 'flex',
  alignItems: 'center',
  marginBottom: theme.spacing(0.5),
  borderRadius: theme.shape.borderRadius,
  ...(isFocus && {
    background: theme.palette.action.selected
  }),
  position: 'relative',
  '&:hover': {
    background: theme.palette.action.hover,
    '& > div:first-of-type': {
      flex: 'unset',
      width: 'calc(100% - 40px)'
    }
  },
  '&:hover .itemSubtitle,&:hover .uiItemUnReadDot': {
    display: 'none'
  },
  '&:hover .uiChatItemBtn': {
    opacity: 1
  },
  ...(isMobile && {
    '.uiChatItemBtn, &:hover .itemSubtitle': {
      opacity: 1
    },
    '& > div:first-of-type': {
      flex: 'unset',
      width: 'calc(100% - 40px)'
    }
  }),
  ...(isActive && {
    '.uiChatItemBtn': {
      opacity: 1
    },
    '.itemSubtitle': {
      display: 'none'
    },
    '& > div:first-of-type': {
      flex: 'unset',
      width: 'calc(100% - 40px)'
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
const ItemRowTitleWrapper = styled('div', {
  shouldForwardProp: props => props !== 'lastMessage'
})<{ lastMessage?: boolean }>(({ theme, lastMessage }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  ...(lastMessage && {
    marginTop: theme.spacing(0.25)
  })
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
  lineHeight: theme.spacing(2.35),
  marginLeft: theme.spacing(1)
}));

const LineIconStyled = styled(LineIcon, {
  shouldForwardProp: props => props !== 'favorite'
})<{ favorite?: boolean }>(({ theme, favorite }) => ({
  marginRight: theme.spacing(0.5),
  ...(favorite && {
    color: theme.palette.primary.main
  })
}));

const UIChatItemBtn = styled(IconButton, {
  slot: 'UIChatItemBtn',
  shouldForwardProp: props => props !== 'isActive'
})<{ isActive?: boolean }>(({ theme, isActive }) => ({
  position: 'relative',
  padding: theme.spacing(1, 0.5),
  cursor: 'pointer',
  minWidth: theme.spacing(3),
  lineHeight: theme.spacing(2.5)
}));

const RootItemAction = styled('div', {
  slot: 'root',
  shouldForwardProp: props => props !== 'isSelfChat'
})<{ isSelfChat?: boolean }>(({ theme, isSelfChat }) => ({
  position: 'absolute',
  right: 8,
  opacity: 0,
  display: 'flex',
  alignItems: 'center',
  ...(isSelfChat && {
    minWidth: '24px',
    minHeight: '40px'
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
  handleResetSearch?: any;
  active?: any;
  setActive?: any;
}

export default function BuddyItem({
  item,
  handleResetSearch,
  active,
  setActive
}: Props) {
  const {
    usePageParams,
    dispatch,
    i18n,
    useActionControl,
    ItemActionMenu,
    useIsMobile,
    navigate,
    useScrollRef
  } = useGlobal();
  const popperRef = React.useRef();
  const pageParams = usePageParams();
  const location = useLocation() as any;
  const scrollRef = useScrollRef();

  const { rid } = pageParams;
  const isMobile = useIsMobile(true);

  const buddy = useSelector((state: GlobalState) =>
    getBuddyItem(state, item?.id)
  );

  const subscription = useSubscriptionItem(item?.id);

  const userChat = useChatUserItem(item?.userId);

  const [handleAction] = useActionControl<State, unknown>(item?.id, {});

  const itemAction = useItemActionRoomItem();
  const chatRoom = useChatRoom(item?.id);
  const room = useRoomItem(item?.id);
  const settings = usePublicSettings();
  const perms = useRoomPermission(item?.id);
  const isSelfChat = useIsSelfChat(item?.id);
  const userSession = useSessionUser();

  const openChatRoom = React.useCallback(
    () => {
      handleResetSearch && handleResetSearch();

      if (item?.no_join || !subscription) {
        if (item?.spotlight_user) {
          dispatch({
            type: 'chatplus/chatRoom/newConversation',
            payload: { users: [{ value: item?.username, label: item?.name }] }
          });

          return;
        }

        dispatch({
          type: 'chatplus/room/openChatRoomFromBuddy',
          payload: { rid: item.id || item._id }
        });

        return;
      }

      if (location.pathname.includes('/messages') || rid) {
        navigate({
          pathname: `/messages/${item.id}`
        });
      } else {
        dispatch({
          type: 'chatplus/room/openBuddy',
          payload: { rid: item.id }
        });
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  const status = isSelfChat ? 1 : userChat?.status || 0;

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

  const unread =
    !item?.no_join &&
    !isEmpty(item?.lastMessage) &&
    (subscription?.alert || !!subscription?.unread);

  const handleClickActive = React.useCallback(() => {
    if (active === item.id) {
      setActive(null);

      return;
    }

    setActive(item.id);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [active, item.id]);

  const onClickAway = React.useCallback(() => {
    if (active === item.id) {
      setActive(null);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [active, item.id]);

  return (
    <ClickOutsideListener excludeRef={popperRef} onClickAway={onClickAway}>
      <RootBuddyItem
        data-testid={camelCase('Buddy Item')}
        unread={unread}
        isFocus={!item?.no_join && rid === item.id}
        isMobile={isMobile}
        isActive={active === item.id}
      >
        <ItemWrapper
          data-testid={camelCase('Buddy Item Wrapper')}
          onClick={openChatRoom}
        >
          <ItemMedia data-testid={camelCase('Buddy Item Media')}>
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
          <ItemInner data-testid={camelCase('Buddy Item Inner')}>
            <ItemRowTitleWrapper data-testid={camelCase('Buddy Item favorite')}>
              {subscription?.f ? (
                <LineIconStyled favorite icon="ico-heart" />
              ) : null}
              <ItemTitle data-testid={camelCase('Buddy Item title')}>
                {buddy?.name || item.fname || item.name}
              </ItemTitle>
              {!item?.no_join ? (
                <ItemSubtitle
                  className="itemSubtitle"
                  data-testid={camelCase('Buddy Item updatedAt')}
                >
                  <FromNowChat
                    value={item?.lastMessage?._updatedAt?.$date}
                    format="ll"
                  />
                </ItemSubtitle>
              ) : null}
            </ItemRowTitleWrapper>
            <LastMessage
              room={room}
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
              scrollRef={scrollRef}
              handleAction={handleAction}
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
    </ClickOutsideListener>
  );
}
