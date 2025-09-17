import { UserAvatar } from '@metafox/ui';
import { Tooltip } from '@mui/material';
import * as React from 'react';

type UserAccessedProps = {
  size?: number;
  user: { full_name: string; avatar: string; user_name: string; id: string };
  onSelectUser: (user: any) => void;
  to?: string;
};

export default function UserAccessed(props: UserAccessedProps) {
  const { size = 64, user, to, onSelectUser } = props;

  return (
    <Tooltip title={user.full_name}>
      <span>
        <UserAvatar
          size={size}
          user={user as any}
          to={to}
          onClick={() => onSelectUser(user)}
          noStory
          showStatus={false}
        />
      </span>
    </Tooltip>
  );
}
