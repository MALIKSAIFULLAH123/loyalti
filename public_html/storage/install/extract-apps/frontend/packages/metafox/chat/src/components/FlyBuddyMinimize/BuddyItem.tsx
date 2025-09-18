import { useRoomItem } from '@metafox/chat/hooks';
import { OpenRoomShape } from '@metafox/chat/types';
import { useGlobal } from '@metafox/framework';
import { LineIcon, UserAvatar } from '@metafox/ui';
import { Badge, IconButton, styled, Tooltip } from '@mui/material';
import React from 'react';

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

const StyledTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
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

const ItemMedia = styled('div', { name })(({ theme }) => ({
  width: '48px',
  height: '48px',
  marginTop: theme.spacing(1),
  border:
    theme.palette.mode === 'dark' ? 'none' : theme.mixins.border('secondary'),
  boxShadow: theme.shadows[2],
  backgroundColor: theme.palette.background.paper,
  borderRadius: '50%'
}));

const Unread = styled('div', { name })(({ theme }) => ({
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
  const { dispatch, useGetItem } = useGlobal();

  const room = useRoomItem(item.rid);

  const userIdentity = room?.other_members[0];

  const user = useGetItem(userIdentity);

  const openChatRoom = React.useCallback(
    () => {
      dispatch({
        type: 'chat/room/toggle',
        payload: { identity: item?.rid }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );
  const closeChatRoom = React.useCallback(
    () => {
      dispatch({
        type: 'chat/closePanel',
        payload: { identity: item?.rid }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  return (
    <ItemViewStyled unread={Boolean(room?.total_unseen)}>
      <StyledTooltip title={room?.name} placement="right">
        <ItemMedia onClick={openChatRoom}>
          <UserAvatar user={user} size={48} noLink />
          {room?.total_unseen ? (
            <Unread className="unread">
              <ItemBadge
                color="error"
                max={99}
                badgeContent={room?.total_unseen || 0}
              />
            </Unread>
          ) : null}
        </ItemMedia>
      </StyledTooltip>
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
    </ItemViewStyled>
  );
}
