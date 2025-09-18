import {
  ChatRoomType,
  RoomItemShape,
  RoomType,
  UserShape,
  UserStatusType
} from '@metafox/chatplus/types';
import { convertTimeActive } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { Badge, styled } from '@mui/material';
import { camelCase, isNumber } from 'lodash';
import React from 'react';

const name = 'DockPanelTitle';

const WrapperTitle = styled('div')(({ theme }) => ({
  flex: 1,
  fontWeight: 'bold',
  display: 'block',
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  maxWidth: '100%'
}));

const Status = styled('span', {
  shouldForwardProp: props => props !== 'status'
})<{ status?: UserStatusType }>(({ theme, status }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.primary,
  lineHeight: theme.spacing(2.375),
  ...(status === UserStatusType.Offline && {
    color: theme.palette.text.secondary
  })
}));

const Children = styled('span', {
  shouldForwardProp: props =>
    props !== 'variant' && props !== 'type' && props !== 'isChatBot'
})<{ type?: ChatRoomType; variant?: string; isChatBot?: boolean }>(
  ({ theme, variant, type, isChatBot }) => ({
    ...theme.typography.subtitle1,
    fontSize: '15px',
    lineHeight: theme.spacing(2.375),
    ...(type === RoomType.Direct && {
      ':hover': {
        cursor: 'pointer',
        textDecoration: 'underline'
      }
    }),
    ...(variant === 'new_message' && {
      fontSize: theme.typography.pxToRem(18),
      ':hover': {
        cursor: 'auto',
        textDecoration: 'none'
      }
    }),
    ...(isChatBot && {
      ':hover': {
        cursor: 'auto',
        textDecoration: 'none'
      }
    })
  })
);

const StyledBadge = styled(Badge, {
  shouldForwardProp: props => props !== 'status'
})<{ status?: number }>(({ theme, status }) => ({
  margin: theme.spacing(0, 1, 0, 0.5),
  '& .MuiBadge-badge': {
    ...(status === UserStatusType.Online && {
      color: theme.palette.success.main,
      backgroundColor: theme.palette.success.main
    }),
    ...(status === UserStatusType.Away && {
      color: theme.palette.warning.main,
      backgroundColor: theme.palette.warning.main
    }),
    ...(status === UserStatusType.Busy && {
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
  },
  ...(status === UserStatusType.Invisible && {
    display: 'none'
  })
}));

const RootStatusTitle = styled('div', { name })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginTop: theme.spacing(0.25)
}));
interface Props {
  children: React.ReactNode;
  status?: number;
  room?: RoomItemShape;
  user?: UserShape;
  variant?: 'new_message' | string;
  isSelfChat?: boolean;
}

const StatusTitle = ({ statusProps, user, isSelfChat }) => {
  const { i18n } = useGlobal();

  if (
    statusProps === UserStatusType.Invisible ||
    (!isSelfChat && !user?.lastStatusUpdated?.$date)
  )
    return null;

  let title = '';
  switch (statusProps) {
    case UserStatusType.Offline: {
      const time = convertTimeActive(user?.lastStatusUpdated?.$date);

      title = time
        ? i18n.formatMessage({ id: 'active_time_ago' }, { time })
        : '';
      break;
    }
    case UserStatusType.Online:
      title = i18n.formatMessage({ id: 'online' });
      break;
    case UserStatusType.Away:
      title = i18n.formatMessage({ id: 'away' });
      break;
    case UserStatusType.Busy:
      title = i18n.formatMessage({ id: 'busy' });
      break;

    default:
      break;
  }

  return (
    <RootStatusTitle>
      {statusProps !== UserStatusType.Offline && (
        <StyledBadge
          overlap="circular"
          anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
          variant="dot"
          status={statusProps}
        />
      )}
      <Status status={statusProps}>{title}</Status>
    </RootStatusTitle>
  );
};

export default function Title({
  room,
  children,
  status,
  user,
  variant,
  isSelfChat = false
}: Props) {
  const { navigate } = useGlobal();

  const isChatBot = room?.isBotRoom;

  const handleClick = () => {
    if (isChatBot) return;

    if (room?.t === RoomType.Direct && user && user.username) {
      navigate(`/${user.username}`);
    }
  };

  if (room?.t === RoomType.Direct) {
    return (
      <WrapperTitle data-testid={camelCase('Chat Dock Header title')}>
        <Children onClick={handleClick} type={room?.t} isChatBot={isChatBot}>
          {children}
        </Children>
        {room?.t === RoomType.Direct && isNumber(status) ? (
          <StatusTitle
            statusProps={status}
            user={user}
            isSelfChat={isSelfChat}
          />
        ) : null}
      </WrapperTitle>
    );
  }

  return (
    <WrapperTitle data-testid={camelCase('Chat Dock Header title')}>
      <Children type={room?.t} variant={variant}>
        {children}
      </Children>
    </WrapperTitle>
  );
}
