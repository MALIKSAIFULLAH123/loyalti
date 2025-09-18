import { RoomType, UserShape } from '@metafox/chatplus/types';
import { conversionStatusStr2Num } from '@metafox/chatplus/utils';
import { RouteLink, useGlobal, Link } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { Badge, Box, styled } from '@mui/material';
import React from 'react';
import Avatar from '../Avatar';

const Root = styled('div', {
  name: 'LayoutSlot',
  slot: 'onlineItem',
  overridesResolver(props, styles) {
    return [styles.onlineItem];
  }
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  color: '#555555',
  fontSize: '14px',
  cursor: 'pointer',
  padding: theme.spacing(1.5, 1),
  '&:hover': {
    borderRadius: theme.spacing(1),
    background:
      theme.palette.mode === 'dark'
        ? theme.palette.grey['600']
        : theme.palette.grey['100']
  }
}));
const ItemRoot = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  width: 'calc(100% - 25px)'
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
  item: UserShape;
}

export default function BuddyItem({ item }: Props) {
  const { dispatch } = useGlobal();

  const openChatRoom = React.useCallback(
    () =>
      dispatch({
        type: 'chatplus/room/createChatRoomFromDirectMessage',
        payload: {
          type: 'username',
          identity: item?.username,
          skipPrivacyCheck: false
        }
      }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  if (item?.invisible) return null;

  const userStatus = item?.status || 0;
  const status = conversionStatusStr2Num(userStatus);

  return (
    <Root onClick={openChatRoom}>
      <ItemRoot>
        <ItemMedia>
          <Avatar
            name={item?.name}
            username={item?.username}
            size={32}
            roomType={RoomType.Direct}
            avatarETag={item?.avatarETag}
            hoverCard={
              item?.metafoxUserId ? `/user/${item?.metafoxUserId}` : false
            }
            component={RouteLink}
          />
        </ItemMedia>
        <TitleName>
          <TruncateText lines={1} variant="h5">
            <Link
              children={item.name}
              hoverCard={
                item?.metafoxUserId ? `/user/${item?.metafoxUserId}` : false
              }
              underline="none"
              color={'inherit'}
            />
          </TruncateText>
        </TitleName>
      </ItemRoot>
      {status ? (
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
