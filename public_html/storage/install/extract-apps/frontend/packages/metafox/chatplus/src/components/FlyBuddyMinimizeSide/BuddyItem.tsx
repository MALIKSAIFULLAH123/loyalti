import { useRoomItem } from '@metafox/chatplus/hooks';
import { getBuddyItem, getSubscriptionItem } from '@metafox/chatplus/selectors';
import { OpenRoomShape } from '@metafox/chatplus/types';
import { GlobalState, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Badge, IconButton, styled, Tooltip, Zoom } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import Avatar from '../Avatar';

const name = 'BuddyItem-Panel';
const ActionButtonStyled = styled(IconButton, { name })(({ theme }) => ({
  right: theme.spacing(-1),
  top: theme.spacing(1),
  position: 'absolute',
  opacity: 0,
  width: theme.spacing(2.75),
  height: theme.spacing(2.75),
  border:
    theme.palette.mode === 'dark' ? 'none' : theme.mixins.border('secondary'),
  boxShadow: theme.shadows[2],
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['50']
      : theme.palette.background.paper,
  borderRadius: '50%',
  zIndex: 999,
  '& span.ico': {
    fontSize: theme.spacing(1.5),
    ...(theme.palette.mode === 'dark' && {
      color: theme.palette.grey['A700']
    })
  },
  '&:hover': {
    backgroundColor:
      theme.palette.mode === 'dark'
        ? `${theme.palette.grey['50']} !important`
        : `${theme.palette.grey['300']} !important`
  }
}));

const ItemViewStyled = styled('div', {
  name,
  shouldForwardProp: props => props !== 'unread'
})<{ unread?: boolean }>(({ theme, unread }) => ({
  position: 'relative',
  cursor: 'pointer',
  '&:hover': {
    '.actionButtonStyled': {
      opacity: 1
    },
    ...(unread && {
      '.unread': {
        opacity: 0
      }
    })
  }
}));

const StyledTooltip = styled(
  ({ className, ...props }: { className?: string }) => (
    <Tooltip {...props} classes={{ popper: className }} />
  )
)(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    ...theme.typography.h5,
    backgroundColor: 'white',
    color: 'black',
    border:
      theme.palette.mode === 'dark' ? 'none' : theme.mixins.border('secondary'),
    boxShadow: theme.shadows[2],
    padding: theme.spacing(1.2)
  },
  '& .MuiTooltip-arrow': {
    '&::before': {
      backgroundColor: 'white',
      border: theme.mixins.border('secondary'),
      boxShadow: theme.shadows[2]
    }
  }
}));

const ItemMedia = styled('div', {
  name
})(({ theme }) => ({
  width: '48px',
  height: '48px',
  marginTop: theme.spacing(1),
  backgroundColor: 'transparent',
  borderRadius: '50%'
}));

const Unread = styled('div', {
  name,
  slot: 'unread'
})(({ theme }) => ({
  position: 'relative',
  top: -48,
  left: 44
}));
const ItemBadge = styled(Badge, { name })(({ theme }) => ({
  fontSize: theme.spacing(2.5)
}));

interface Props {
  item: OpenRoomShape;
  classes?: any;
}

export default function BuddyItem({ item }: Props) {
  const { dispatch } = useGlobal();

  const buddy = useSelector((state: GlobalState) =>
    getBuddyItem(state, item.rid)
  );

  const subscription = useSelector((state: GlobalState) =>
    getSubscriptionItem(state, item.rid)
  );
  const room = useRoomItem(item.rid);

  const openChatRoom = React.useCallback(
    () => {
      if (room?.isBotRoom) {
        dispatch({
          type: 'chatplus/openRooms/addRoomToChatDock',
          payload: { rid: item?.rid }
        });

        return;
      }

      dispatch({
        type: 'chatplus/room/toggle',
        payload: { identity: item?.rid, isMarkRead: true }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );
  const closeChatRoom = React.useCallback(
    () => {
      dispatch({
        type: 'chatplus/closePanel',
        payload: { identity: item?.rid }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  const { name, avatar, username, avatarETag } = buddy || {};

  return (
    <Zoom in>
      <ItemViewStyled
        unread={!room?.isBotRoom && Boolean(subscription?.unread)}
      >
        <StyledTooltip title={name} placement="right">
          <ItemMedia onClick={openChatRoom}>
            <Avatar
              name={name}
              username={username}
              size={48}
              src={avatar}
              room={room}
              avatarETag={avatarETag}
            />
            {subscription?.unread ? (
              <Unread className="unread">
                <ItemBadge
                  color="error"
                  max={99}
                  badgeContent={subscription?.unread || 0}
                />
              </Unread>
            ) : null}
          </ItemMedia>
        </StyledTooltip>
        {room?.isBotRoom ? null : (
          <ActionButtonStyled
            onClick={closeChatRoom}
            className="actionButtonStyled"
            aria-label="action"
            size="small"
            color="primary"
            variant="white-contained"
          >
            <LineIcon icon={'ico-close'} />
          </ActionButtonStyled>
        )}
      </ItemViewStyled>
    </Zoom>
  );
}
