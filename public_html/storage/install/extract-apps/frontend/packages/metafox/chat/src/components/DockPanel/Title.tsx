import { ChatRoomType, RoomType } from '@metafox/chat/types';
import { useGlobal } from '@metafox/framework';
import { Skeleton, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const WrapperTitle = styled('div')(({ theme }) => ({
  flex: 1,
  fontWeight: 'bold',
  display: 'block',
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  maxWidth: '100%'
}));

const Children = styled('span', {
  shouldForwardProp: props => props !== 'variant'
})<{ type?: ChatRoomType; variant?: string }>(({ theme, variant, type }) => ({
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
  })
}));

const LoadingSkeleton = () => {
  return (
    <WrapperTitle>
      <Skeleton variant="text" width={60} />
    </WrapperTitle>
  );
};

export default function Title({
  room,
  children,
  status,
  user,
  variant,
  isSelfChat = false,
  loading = false
}: any) {
  const { navigate } = useGlobal();

  if (loading) <LoadingSkeleton />;

  const handleClick = () => {
    const _user = user && (user.username || user.user_name);

    if (room?.t === RoomType.Direct && _user) {
      navigate(`/${_user}`);
    }
  };

  if (room?.t === RoomType.Direct) {
    return (
      <WrapperTitle data-testid={camelCase('_chat Dock Header title')}>
        <Children onClick={handleClick} type={room?.t}>
          {children}
        </Children>
      </WrapperTitle>
    );
  }

  return (
    <WrapperTitle data-testid={camelCase('_chat Dock Header title')}>
      <Children type={room?.t} variant={variant}>
        {children}
      </Children>
    </WrapperTitle>
  );
}
