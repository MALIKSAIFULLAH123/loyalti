import FromNowChat from '@metafox/chat/FromNowChat';
import { useItemActionRoomItem } from '@metafox/chat/hooks';
import { formatLastMsg } from '@metafox/chat/services/formatTextMsg';
import { RoomItemShape } from '@metafox/chat/types';
import { useActionControl, useGlobal, useLocation } from '@metafox/framework';
import {
  ItemAction,
  ItemActionMenu,
  ItemMedia,
  ItemTitle,
  ItemView,
  ItemViewProps,
  LineIcon,
  UserAvatar
} from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { IconButton, styled, Tooltip } from '@mui/material';
import { isArray } from 'lodash';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const name = 'ItemView_BlockChatRoom';

const RootBuddyItem = styled(ItemView, {
  name,
  slot: 'rootBuddyItemChat',
  overridesResolver(props, styles) {
    return [styles.rootBuddyItemChat];
  },
  shouldForwardProp: props =>
    props !== 'unread' && props !== 'isActive' && props !== 'variant'
})<{
  unread?: number;
  isActive?: boolean;
  variant?: string;
}>(({ theme, unread, isActive, variant }) => ({
  cursor: 'pointer',
  transition: 'background-color 300ms ease',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  ...((unread || isActive) && {
    background: theme.palette.action.selected,
    borderRadius: theme.shape.borderRadius
  }),
  '& .MuiAvatar-root': {
    fontSize: theme.mixins.pxToRem(16)
  },
  ...(variant !== 'popper' && {
    '&:hover .itemSubtitle': {
      visibility: 'hidden',
      transition: 'visibility 0s linear 0s, opacity 300ms'
    },
    '&:hover': {
      background: theme.palette.action.hover,
      '& > div:first-of-type': {
        flex: 'unset',
        width: 'calc(100% - 40px)'
      },
      '& .uiChatItemBtn': {
        opacity: 1
      }
    }
  }),
  '&:hover .uiItemUnReadDot': {
    display: 'none'
  }
}));
const ItemWrapper = styled('div', { name, slot: 'ItemWrapper' })(
  ({ theme }) => ({
    flex: 1,
    display: 'flex',
    alignItems: 'center',
    padding: theme.spacing(1.5, 0),
    color: theme.palette.grey['700'],
    fontSize: theme.spacing(1.75),
    cursor: 'pointer',
    overflow: 'hidden'
  })
);

const ItemInner = styled('div')(({ theme }) => ({
  flex: 1,
  minWidth: '0'
}));

const ItemRowTitleWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between'
}));

const ItemTitleStyled = styled(ItemTitle)(({ theme }) => ({
  flex: 1,
  marginRight: theme.spacing(1)
}));

const StyledItemLastMsg = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between'
}));

const ItemSubtitle = styled('div')(({ theme }) => ({
  color: theme.palette.text.secondary,
  display: 'inline-flex',
  alignItems: 'center',
  fontSize: theme.spacing(1.625),
  lineHeight: theme.spacing(2.35)
}));

const LineIconStyled = styled(LineIcon)(({ theme }) => ({
  marginRight: theme.spacing(0.5)
}));
const ItemMsgText = styled('div', {
  shouldForwardProp: props => props !== 'unread'
})<{ unread?: any }>(({ theme, unread }) => ({
  display: 'block',
  color: theme.palette.text.secondary,
  padding: 0,
  minWidth: 0,
  maxWidth: 'calc(100% - 12px)',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  fontSize: theme.spacing(1.625),
  lineHeight: theme.spacing(2.35),
  whiteSpace: 'nowrap',
  ...(unread && {
    fontWeight: theme.typography.fontWeightBold
  }),
  br: {
    display: 'none'
  }
}));

const UIChatItemBtn = styled(IconButton, {
  slot: 'UIChatItemBtn',
  shouldForwardProp: props => props !== 'variant'
})<{ variant?: string }>(({ theme, variant }) => ({
  position: 'relative',
  padding: theme.spacing(1, 0.5),
  cursor: 'pointer',
  minWidth: theme.spacing(3),
  lineHeight: theme.spacing(2.5),
  ...(variant === 'popper' && {
    width: '35px',
    height: '35px',
    borderRadius: 40,
    background: theme.palette.background.paper
  })
}));

const UnReadDot = styled('span', { name, slot: 'UnReadDot' })(({ theme }) => ({
  display: 'inline-block',
  width: 12,
  height: 12,
  backgroundColor: theme.palette.primary.main,
  borderRadius: 20,
  marginTop: theme.spacing(0.25)
}));

const ItemActionStyled = styled(ItemAction, {
  name,
  slot: 'ItemAction',
  shouldForwardProp: props => props !== 'isSelfChat' && props !== 'variant'
})<{ isSelfChat?: boolean; variant?: string }>(
  ({ theme, isSelfChat, variant }) => ({
    position: 'absolute',
    right: 8,
    opacity: 0,
    '& .MuiActionMenu-menu': {
      overflow: 'auto',
      maxHeight: '200px'
    },
    ...(isSelfChat && {
      minWidth: '24px',
      minHeight: '40px'
    }),
    ...(variant === 'popper' && {
      right: theme.spacing(1),
      top: '50%',
      transform: 'translateY(-50%)',
      position: 'absolute'
    })
  })
);

export interface BlockedUserItemShape {
  resource_name: string;
  module_name: string;
  user_name: string;
  full_name: string;
  avatar: string;
  is_featured: boolean;
  id: number;
  is_blocked: boolean;
}
export interface BlockedUserAction {
  unblockItem: any;
}

interface State {}

const LastMessage = ({ item, unread }: any) => {
  const { i18n, useSession } = useGlobal();

  const { user: authUser } = useSession();
  const user = item?.user;
  const isOwner = authUser?.id === user?.id;

  if (!item && !unread) return null;

  let lastMessage: any = formatLastMsg(item, user, isOwner);

  if (
    !isArray(lastMessage) &&
    item?.attachments &&
    item.attachments?.length > 0
  ) {
    const countImageAttachment = item.attachments.filter(
      item => item?.is_image
    ).length;

    lastMessage =
      item.attachments.length === countImageAttachment ? (
        <>
          <LineIconStyled icon="ico-photo" />
          {countImageAttachment > 1
            ? i18n.formatMessage({ id: 'sent_multiple_photos' })
            : i18n.formatMessage({ id: 'sent_a_photo' })}
        </>
      ) : (
        <>
          <LineIconStyled icon="ico-paperclip-alt" />
          {i18n.formatMessage({ id: 'sent_a_file' })}
        </>
      );
  }

  return (
    <StyledItemLastMsg>
      <ItemMsgText unread={unread}>
        {item && lastMessage ? (
          <>
            {user?.full_name}:{' '}
            {isArray(lastMessage) ? (
              i18n.formatMessage(
                { id: lastMessage[0]?.id || 'message_was_deleted' },
                { user: lastMessage[0]?.user }
              )
            ) : item?.attachments?.length ? (
              lastMessage
            ) : (
              <span dangerouslySetInnerHTML={{ __html: lastMessage }} />
            )}
          </>
        ) : null}
      </ItemMsgText>
      {unread ? (
        <ItemSubtitle>
          <UnReadDot className="uiItemUnReadDot" />
        </ItemSubtitle>
      ) : null}
    </StyledItemLastMsg>
  );
};

export default function ItemViewBlockChatRoom({
  item,
  actions,
  wrapAs,
  wrapProps,
  itemProps
}: ItemViewProps<RoomItemShape>) {
  const {
    usePageParams,
    navigate,
    dispatch,
    useGetItem,
    usePopover,
    i18n,
    useScrollRef
  } = useGlobal();
  const { closePopover } = usePopover();
  const pageParams = usePageParams();
  const scrollRef = useScrollRef();
  const location = useLocation() as any;

  const { rid } = pageParams;
  const userIdentity = item?.other_members[0];

  const user = useGetItem(userIdentity);
  const lastMessageItem = item?.last_message;

  const itemAction = useItemActionRoomItem();

  const items = React.useMemo(
    () => filterShowWhen(itemAction, { item }),
    [itemAction, item]
  );

  const [handleAction] = useActionControl<State, unknown>(item?.id, {});

  const openChatRoom = React.useCallback(
    () => {
      if (location.pathname.includes('/messages') || rid) {
        navigate({
          pathname: `/messages/${item.id}`
        });
      } else {
        dispatch({
          type: 'chat/openRoomPanel',
          payload: { rid: item.id }
        });
        closePopover && closePopover();
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  if (!item) return null;

  return (
    <RootBuddyItem
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={item.id}
      isActive={Boolean(rid == item.id)}
      variant={itemProps?.type}
    >
      <ItemWrapper onClick={openChatRoom}>
        <ItemMedia>
          <UserAvatar user={user} size={40} noLink />
        </ItemMedia>
        <ItemInner>
          <ItemRowTitleWrapper>
            <ItemTitleStyled>{item?.name}</ItemTitleStyled>
            <ItemSubtitle className="itemSubtitle">
              <FromNowChat value={lastMessageItem?.updated_at} format="ll" />
            </ItemSubtitle>
          </ItemRowTitleWrapper>

          <LastMessage item={lastMessageItem} unread={item?.total_unseen} />
        </ItemInner>
      </ItemWrapper>
      {itemProps?.showActionMenu ? (
        <ItemActionStyled
          visible="hover"
          variant={itemProps?.type}
          className="uiChatItemBtn"
        >
          <ItemActionMenu
            items={items}
            placement="bottom-end"
            handleAction={handleAction}
            scrollRef={scrollRef}
            popperOptions={{
              strategy: 'fixed'
            }}
            control={
              <Tooltip
                title={i18n.formatMessage({ id: 'more' })}
                placement="top"
              >
                <UIChatItemBtn
                  variant={itemProps?.type}
                  disableFocusRipple
                  disableRipple
                  disableTouchRipple
                >
                  <LineIcon icon="ico-dottedmore-vertical-o" />
                </UIChatItemBtn>
              </Tooltip>
            }
          />
        </ItemActionStyled>
      ) : null}
    </RootBuddyItem>
  );
}

ItemViewBlockChatRoom.displayName = 'Blocked_ChatRoomCard';
ItemViewBlockChatRoom.LoadingSkeleton = LoadingSkeleton;
