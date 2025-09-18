import { useGlobal } from '@metafox/framework';
import { UserAvatar } from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import { styled, Tooltip } from '@mui/material';
import React, { memo } from 'react';

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
  identity?: string;
  user?: UserItemShape;
  size?: number;
  showTooltip?: boolean;
}

function MsgAvatar({
  identity,
  size = 30,
  user,
  showTooltip = false
}: AvatarProps) {
  const { useGetItem } = useGlobal();

  const userItem = useGetItem(identity);

  if (!user && !userItem) return null;

  const tooltip = showTooltip && (user?.full_name || userItem?.full_name);

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
        <UserAvatar user={user || userItem} size={size} noLink />
      </div>
    </StyledTooltip>
  );
}

export default memo(MsgAvatar);
