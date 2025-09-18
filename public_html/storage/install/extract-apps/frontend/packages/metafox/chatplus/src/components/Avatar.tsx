/**
 * @type: ui
 * name: ChatAvatar
 */
import { Link, useGlobal } from '@metafox/framework';
import { colorHash, shortenFullName } from '@metafox/utils';
import {
  Avatar as MuiAvatar,
  AvatarProps,
  Badge,
  styled,
  useTheme,
  Box
} from '@mui/material';
import React, { memo } from 'react';
import {
  ChatplusConfig,
  ChatRoomType,
  ChatUserStatus,
  RoomItemShape,
  RoomType,
  UserStatusType
} from '@metafox/chatplus/types';
import { conversionStatusStr2Num } from '../utils';
import qs from 'query-string';
import { camelCase } from 'lodash';

const AvatarWrapper = styled(MuiAvatar, { name: 'AvatarWrapper' })(
  ({ theme }) => ({
    borderWidth: 'thin',
    borderStyle: 'solid',
    borderColor: theme.palette.border.secondary
  })
);

const LinkStyled = styled(Link, { name: 'Link' })(({ theme }) => ({
  '&:hover': {
    textDecoration: 'none'
  }
}));

const StyledBadge = styled(Badge, {
  shouldForwardProp: props => props !== 'status'
})<{ status?: number }>(({ theme, status }) => ({
  '& .MuiBadge-badge': {
    ...(status === UserStatusType.Offline && {
      display: 'none'
    }),
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
    boxShadow:
      theme.palette.mode === 'dark'
        ? 'none'
        : `0 0 0 2px ${theme.palette.background.paper}`,
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

const WrapperAvatarGroup = styled('div', {
  shouldForwardProp: props => props !== 'sizeProps'
})<{ sizeProps?: any }>(({ theme, sizeProps }) => ({
  position: 'relative',
  width: '32px',
  height: '32px',
  ...(sizeProps && {
    width: sizeProps,
    height: sizeProps
  })
}));
const AvatarL1 = styled(MuiAvatar, {
  shouldForwardProp: props => props !== 'sizeProps'
})<{ sizeProps?: any }>(({ theme, sizeProps }) => ({
  position: 'absolute',
  bottom: 0,
  left: 0,
  width: '26px',
  height: '26px',
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['600']
      : theme.palette.grey['300'],
  borderWidth: 1,
  borderStyle: 'solid',
  borderColor:
    theme.palette.mode === 'light'
      ? theme.palette.grey['50']
      : theme.palette.grey['800'],
  zIndex: 2,
  ...(sizeProps && {
    width: sizeProps * 0.7,
    height: sizeProps * 0.7
  })
}));

const WrapperAvatarL2 = styled(Box, {
  shouldForwardProp: props => props !== 'sizeProps'
})<{ sizeProps?: any }>(({ theme, sizeProps }) => ({
  position: 'relative',
  width: '100%',
  height: '100%'
}));
const AvatarL2 = styled(MuiAvatar, {
  shouldForwardProp: props => props !== 'sizeProps'
})<{ sizeProps?: any }>(({ theme, sizeProps }) => ({
  position: 'absolute',
  top: '50%',
  right: 0,
  transform: 'translate(0%, -50%)',
  width: '26px',
  height: '26px',
  ...(sizeProps && {
    width: `${sizeProps * 0.7}px !important`,
    height: `${sizeProps * 0.7}px !important`
  })
}));

interface Props extends AvatarProps {
  username: string;
  name: string;
  alt?: string;
  size?: number | string | 'xs' | 'sm' | 'md' | 'lg' | '10' | '21';
  to?: string;
  onClick?: any;
  src?: string;
  roomType?: ChatRoomType;
  status?: number | ChatUserStatus;
  room?: RoomItemShape;
  variant?: 'direct' | 'group' | string;
  uploadLocal?: boolean;
  avatarETag?: string;
  component?: any;
  target?: any;
  noLink?: boolean;
}

function Avatar({
  username = 'Name',
  name = 'Name',
  src: srcProp,
  size,
  to: toProp,
  onClick,
  status = 0,
  room,
  uploadLocal = false,
  roomType = null,
  avatarETag,
  component = 'span',
  target = '_self',
  noLink: noLinkProp
}: Props) {
  const { getSetting } = useGlobal();
  const theme = useTheme();

  let noLink = noLinkProp;
  let to = toProp;

  const typeRoom = roomType || room?.t;

  const shortName = shortenFullName(name);
  const style: React.CSSProperties = {
    width: size,
    height: size,
    color: theme.palette.grey['50'],
    fontSize: size / 3
  };

  const setting = getSetting<ChatplusConfig>('chatplus');
  const serverChat = setting?.server?.replace(/\/$/, '');
  let src = srcProp;
  const params = {
    etag: avatarETag
  };

  if (!src) {
    src = avatarETag
      ? `${serverChat}/avatar/${username}?${qs.stringify(params)}`
      : '';
  }

  if (shortName) {
    style.backgroundColor = colorHash.hex(shortName);
  }

  if (room?.isBotRoom) {
    to = null;
    noLink = true;
  }

  if (to) {
    return (
      <LinkStyled target={target} to={to ? to : `/${username}`}>
        <AvatarWrapper
          data-testid={camelCase('chatplus avatar')}
          src={src}
          alt={name}
          style={style}
          component={component}
          children={shortName}
        />
      </LinkStyled>
    );
  }

  // avatar group
  if ([RoomType.Private, RoomType.Public].includes(typeRoom)) {
    if (uploadLocal) {
      return (
        <AvatarWrapper
          data-testid={camelCase('chatplus avatar')}
          src={src}
          alt={name}
          style={style}
          component={component}
          children={shortName}
        />
      );
    }

    if (room?.avatarETag) {
      src = `${serverChat}/avatar/room/${room?.id}?${qs.stringify({
        etag: room?.avatarETag
      })}`;

      return (
        <AvatarWrapper
          data-testid={camelCase('chatplus avatar')}
          src={src}
          alt={name}
          style={style}
          component={component}
          children={shortName}
        />
      );
    } else {
      const avatarOwner = `${serverChat}/avatar/${room?.u?.username}`;

      return (
        <WrapperAvatarGroup
          data-testid={camelCase('chatplus avatar')}
          sizeProps={size}
        >
          <AvatarL1
            sizeProps={size}
            src={avatarOwner}
            alt={name}
            component={component}
            children={shortName}
          />
          <WrapperAvatarL2>
            <AvatarL2
              sizeProps={size}
              src={src}
              alt={name}
              style={style}
              component={component}
              children={shortName}
            />
          </WrapperAvatarL2>
        </WrapperAvatarGroup>
      );
    }
  }

  // avatar direct + status
  if (typeRoom === RoomType.Direct && status) {
    const statusTmp = conversionStatusStr2Num(status);

    return (
      <StyledBadge
        overlap="circular"
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
        variant="dot"
        status={statusTmp}
      >
        <MuiAvatar
          data-testid={camelCase('chatplus avatar')}
          src={src}
          alt={name}
          style={style}
          component={component}
          children={shortName}
        />
      </StyledBadge>
    );
  }

  // default
  return (
    <AvatarWrapper
      data-testid={camelCase('chatplus avatar')}
      src={src}
      alt={name}
      style={style}
      component={component}
      children={shortName}
      onClick={onClick}
      to={noLink ? null : username}
    />
  );
}

export default memo(Avatar);
