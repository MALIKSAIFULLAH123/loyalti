import { useChatUserItem, useSessionUser } from '@metafox/chatplus/hooks';
import {
  getBuddyItem,
  getSubscriptionsSelector
} from '@metafox/chatplus/selectors';
import {
  RoomItemShape,
  RoomType,
  UserStatusType
} from '@metafox/chatplus/types';
import { conversionStatusStr2Num } from '@metafox/chatplus/utils';
import { GlobalState, useGlobal } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { Badge, Box, styled } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import Avatar from '../Avatar';

const Root = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  color: '#555555',
  fontSize: '14px',
  cursor: 'pointer',
  padding: theme.spacing(1.5, 2),
  '&:hover': {
    background:
      theme.palette.mode === 'dark'
        ? theme.palette.grey['600']
        : theme.palette.grey['100']
  }
}));
const ItemRoot = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  width: '100%'
}));
const ItemMedia = styled('div')(({ theme }) => ({
  marginRight: theme.spacing(1)
}));

const TitleName = styled(Box)(({ theme }) => ({
  color: theme.palette.text.primary,
  overflow: 'hidden'
}));

const StyledBadge = styled(Badge, {
  shouldForwardProp: props => props !== 'status'
})<{ status?: number | string }>(({ theme, status }) => ({
  marginRight: theme.spacing(1.5),
  '& .MuiBadge-badge': {
    ...(status === 1 && {
      color: theme.palette.success.main,
      backgroundColor: theme.palette.success.main
    }),
    ...(status === 2 && {
      color: theme.palette.warning.main,
      backgroundColor: theme.palette.warning.main
    }),
    ...(status === 3 && {
      color: theme.palette.error.main,
      backgroundColor: theme.palette.error.main
    }),
    '&::after': {
      position: 'absolute',
      top: 0,
      left: 0,
      width: '100%',
      height: '100%',
      borderRadius: '50%',
      animation: 'ripple 1.2s infinite ease-in-out',
      border: '1px solid currentColor',
      content: '""'
    }
  }
}));

interface Props {
  item: RoomItemShape;
}

export default function BuddyItem({ item }: Props) {
  const { dispatch } = useGlobal();
  const buddy = useSelector((state: GlobalState) =>
    getBuddyItem(state, item.id)
  );

  const subscription = useSelector(getSubscriptionsSelector);
  const user = useSessionUser();
  let userId = item.userId;

  if (!userId && item.t === RoomType.Direct && item?.usersCount === 2) {
    userId = item.uids.filter(u => u.id !== user._id)[0];
  }

  let userChat = useChatUserItem(userId);

  if (item?.usersCount === 1) userChat = user;

  const userStatus = item?.t === RoomType.Direct ? userChat?.status : null;
  const status = conversionStatusStr2Num(userStatus);

  const openChatRoom = React.useCallback(
    () =>
      dispatch({
        type: 'chatplus/room/openBuddy',
        payload: { rid: item.id, userId: item.id }
      }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  if (subscription[item._id].open === false) return null;

  const { name, avatar } = buddy || {};

  return (
    <Root onClick={openChatRoom}>
      <ItemRoot>
        <ItemMedia>
          <Avatar
            name={name}
            username={name}
            size={32}
            src={avatar}
            room={item}
            roomType={RoomType.Direct}
          />
        </ItemMedia>
        <TitleName>
          <TruncateText lines={1} variant="h5">
            {name}
          </TruncateText>
        </TitleName>
      </ItemRoot>
      {status !== UserStatusType.Offline ? (
        <StyledBadge
          overlap="circular"
          anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
          variant="dot"
          status={status}
        />
      ) : null}
    </Root>
  );
}
