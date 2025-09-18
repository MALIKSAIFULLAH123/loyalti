import {
  ChatUserStatus,
  RoomItemShape,
  RoomType
} from '@metafox/chatplus/types';
import { styled, Tooltip } from '@mui/material';
import React, { memo } from 'react';
import Avatar from '../Avatar';

const StyledTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    '&.MuiTooltip-tooltipPlacementLeft': {
      marginRight: `${theme.spacing(1)} !important`
    }
  }
}));
interface AvatarProps {
  allowLink?: boolean;
  size: string | number;
  _id?: string;
  username: string;
  name?: string;
  status?: ChatUserStatus;
  room?: RoomItemShape;
  avatarETag?: string;
  hoverCard?: boolean | string;
  showTooltip?: boolean;
}

function MsgAvatar({
  username,
  name,
  size = 'sm',
  status = null,
  room = null,
  avatarETag,
  hoverCard,
  showTooltip = false
}: AvatarProps) {
  const tooltip = showTooltip && (name || username);

  return (
    <StyledTooltip
      title={tooltip}
      placement="left"
      arrow={false}
      PopperProps={{
        popperOptions: {
          strategy: 'fixed'
        }
      }}
    >
      <div>
        <Avatar
          username={username}
          size={size}
          name={name}
          roomType={RoomType.Direct}
          status={status}
          room={room}
          avatarETag={avatarETag}
          hoverCard={hoverCard}
        />
      </div>
    </StyledTooltip>
  );
}

export default memo(MsgAvatar);
